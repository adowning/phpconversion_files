import "reflect-metadata";
import express, { Request, Response, NextFunction } from 'express';
import bodyParser from 'body-parser';
import { PrismaClient, User, Game, Shop } from '@prisma/client';
import axios from 'axios';
import pino from 'pino';
import pinoHttp from 'pino-http';

const logger = pino({
  level: process.env.LOG_LEVEL || 'info',
  transport: process.env.NODE_ENV !== 'production' ? { target: 'pino-pretty', options: { colorize: true, ignore: 'pid,hostname' } } : undefined,
});
const httpLogger = pinoHttp({ logger });

const prisma = new PrismaClient();
const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(httpLogger); // Use pino-http for request logging
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

interface SpinRequestData {
    userId: string;
    bet_betlevel?: number;
    bet_denomination?: number;
    coin?: number;
    bet?: number;
}

app.post('/api/v1/games/:gameName/spin', async (req: Request, res: Response) => {
    const { gameName } = req.params;
    const requestPayload = req.body as SpinRequestData;
    const reqId = (req as any).id; // Get request ID from pino-http

    logger.info({ reqId, gameName, userId: requestPayload.userId }, "Spin request received");

    if (!requestPayload.userId) {
        logger.warn({ reqId, gameName }, "Missing userId in request.");
        return res.status(400).json({ error: "Missing userId in request.", errorId: reqId });
    }

    let user: User | null;
    let game: Game | null;
    let shop: Shop | null;

    try {
        user = await prisma.user.findUnique({ where: { id: requestPayload.userId } });
        if (!user) {
            logger.warn({ reqId, userId: requestPayload.userId }, "User not found.");
            return res.status(404).json({ error: `User with ID ${requestPayload.userId} not found.`, errorId: reqId });
        }
        if (user.shop_id === null) {
            logger.warn({ reqId, userId: user.id }, "User does not have a shop_id.");
            return res.status(400).json({ error: `User ${user.id} does not have a shop_id.`, errorId: reqId });
        }
        game = await prisma.game.findUnique({ where: { name: gameName } });
        if (!game) {
            logger.warn({ reqId, gameName }, "Game not found.");
            return res.status(404).json({ error: `Game '${gameName}' not found.`, errorId: reqId });
        }
        shop = await prisma.shop.findUnique({ where: { id: user.shop_id } });
        if (!shop) {
            logger.warn({ reqId, shopId: user.shop_id }, "Shop not found.");
            return res.status(404).json({ error: `Shop with ID ${user.shop_id} not found.`, errorId: reqId });
        }
    } catch (dbError: any) {
        logger.error({ err: dbError, reqId, userId: requestPayload.userId, gameName }, "Database error during data fetching");
        return res.status(500).json({ error: "Database error during data fetching.", errorId: reqId });
    }

    let gameStateData: any = {
        action: "spin", playerId: user.id, balance: user.balance, bank: game.current_bank,
        gameData: user.gameSessions && typeof user.gameSessions === 'object' && user.gameSessions !== null && gameName in user.gameSessions ? user.gameSessions[gameName] : {},
        user: { shop_id: user.shop_id, count_balance: user.balance },
        shop: { id: shop.id, percent: shop.percent, max_win: shop.max_win, currency: shop.currency || "USD" },
        game: {
            id: game.id, name: game.name, bet: game.bet_options?.join(','), denomination: game.denomination,
            rezerv: game.rezerv, lines_percent_config_spin: game.lines_percent_config_spin,
            lines_percent_config_bonus: game.lines_percent_config_bonus, advanced: game.advanced_settings,
            denominations_list: game.denominations_list?.join(','), increaseRTP: game.increaseRTP,
            slotFastStop: game.slotFastStop, slotJackPercent: game.slotJackPercent, slotJackpot: game.slotJackpot,
        },
        currency: shop.currency || "USD", jpgs: [], postData: {}
    };

    if (gameName === "NarcosNET") {
        gameStateData.postData = {
            action: "spin",
            bet_betlevel: requestPayload.bet_betlevel || 1,
            bet_denomination: requestPayload.bet_denomination || game.denomination
        };
        gameStateData.gameDataStatic = game.advanced_settings || {};
    } else if (gameName === "DiscoFruitsNG") {
        gameStateData.action = "SpinRequest"; // Override action for DiscoFruitsNG
        gameStateData.gameData = { // DiscoFruitsNG specific nesting for gameData
            ...(gameStateData.gameData || {}), // Preserve existing game session data
            cmd: 'SpinRequest', // Command for DiscoFruitsNG
            data: { // Data payload for DiscoFruitsNG
                coin: requestPayload.coin || game.denomination,
                bet: requestPayload.bet || (game.bet_options?.[0] ? parseFloat(game.bet_options[0]) : 1),
            }
        };
        delete gameStateData.postData; // Remove generic postData as DiscoFruitsNG uses specific structure
    } else {
         logger.warn({ reqId, gameName }, "Game not supported for spin action during gameStateData assembly.");
        return res.status(400).json({ error: `Game '${gameName}' is not supported for spin action.`, errorId: reqId });
    }

    const phpEngineBaseUrl = process.env.PHP_ENGINE_BASE_URL || 'http://localhost:8080';
    let phpEngineUrl = `${phpEngineBaseUrl}/${gameName}/index.php`; // Simplified URL construction

    try {
        logger.debug({ reqId, url: phpEngineUrl, gameStateData }, "Calling PHP engine");
        const phpResponse = await axios.post(phpEngineUrl, gameStateData, { headers: { 'Content-Type': 'application/json' } });
        logger.debug({ reqId, phpResponseData: phpResponse.data }, "Received response from PHP engine");

        const phpResult = phpResponse.data;
        if (!phpResult || typeof phpResult.newBalance === 'undefined' || typeof phpResult.newBank === 'undefined') {
            logger.error({ reqId, phpResult }, "Invalid or incomplete response structure from PHP engine");
            return res.status(500).json({ error: "Invalid response from game engine.", errorId: reqId });
        }

        const updatedUser = await prisma.user.update({
            where: { id: user.id }, data: { balance: parseFloat(phpResult.newBalance) },
        });
        const updatedGame = await prisma.game.update({
            where: { id: game.id }, data: { current_bank: parseFloat(phpResult.newBank) },
        });

        let updatedGameSessionForUser = {};
        if (typeof phpResult.newGameData === 'object' && phpResult.newGameData !== null) {
            updatedGameSessionForUser = phpResult.newGameData;
        } else if (typeof gameStateData.gameData === 'object' && gameStateData.gameData !== null) {
            // If phpResult.newGameData is not what we want, maybe original gameData from PHP was nested
            // This part might need game-specific logic if PHP returns game data differently
            updatedGameSessionForUser = gameStateData.gameData;
        }

        const newGameSessions = {
            ...(user.gameSessions && typeof user.gameSessions === 'object' ? user.gameSessions : {}),
            [gameName]: updatedGameSessionForUser
        };

        await prisma.user.update({ where: { id: user.id }, data: { gameSessions: newGameSessions } });

        let betAmountForLog = 0;
        if (gameName === "NarcosNET") {
            betAmountForLog = (gameStateData.postData?.bet_betlevel || 0) * 20; // Assuming 20 lines
        } else if (gameName === "DiscoFruitsNG") {
             betAmountForLog = (gameStateData.gameData?.data?.bet || 0) * (gameStateData.gameData?.data?.coin || 1);
        }


        await prisma.gameLog.create({
            data: {
                userId: user.id, gameId: game.id, request_details: gameStateData, response_details: phpResult,
                bet_amount: betAmountForLog,
                win_amount: parseFloat(String(phpResult.totalWin || 0)),
                user_balance_before: user.balance, user_balance_after: updatedUser.balance,
                game_bank_before: game.current_bank, game_bank_after: updatedGame.current_bank,
            },
        });
        logger.info({ reqId, userId: user.id, gameId: game.id }, "Database updated successfully after spin.");

        res.json({
            message: `Spin successful for game '${gameName}'.`, newBalance: updatedUser.balance,
            totalWin: phpResult.totalWin, reels: phpResult.reels, updatedGameData: phpResult.newGameData
        });
    } catch (error: any) {
        logger.error({ err: error, reqId, gameName, userId: requestPayload.userId }, "Error during PHP engine call or DB persistence");
        if (axios.isAxiosError(error) && error.response) {
            logger.error({ phpResponseStatus: error.response.status, phpResponseData: error.response.data }, "PHP Engine Response Error Details");
            return res.status(500).json({ error: `Error from PHP engine.`, errorId: reqId, details: error.response.data });
        }
        return res.status(500).json({ error: `Internal server error.`, errorId: reqId });
    }
});

// Global error handler (fallback)
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
    const reqId = (req as any).id;
    logger.error({ err, reqId }, "Unhandled API error");
    if (!res.headersSent) { // Check if response has already been sent
       res.status(500).json({ error: 'Something broke unexpectedly!', errorId: reqId });
    }
});

const server = app.listen(port, () => {
    logger.info(`TypeScript Facade Server listening on port ${port}`);
});

process.on('SIGTERM', () => {
    logger.info('SIGTERM signal received: closing HTTP server')
    server.close(async () => {
        logger.info('HTTP server closed')
        await prisma.$disconnect()
        process.exit(0)
    })
});
process.on('SIGINT', () => {
    logger.info('SIGINT signal received: closing HTTP server')
    server.close(async () => {
        logger.info('HTTP server closed')
        await prisma.$disconnect()
        process.exit(0)
    })
});
export default app;
