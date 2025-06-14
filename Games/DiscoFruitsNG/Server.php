<?php

namespace VanguardLTE\Games\DiscoFruitsNG {
    set_time_limit(5);
    class Server
    {
        public function get($request, $game,  $userId = null)
        {
            // If no userId is passed (like from our new API endpoint),
            // fall back to the session-based Auth::id() for the original web flow.
            if ($userId === null) {
                $userId = \Auth::id();
                if ($userId === null) {
                    $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                    exit($response);
                }
            }

            // The logic from the old get_() function is now safely inside this transaction closure.
            \DB::transaction(function () use ($request, $game, $userId) {

                try {
                    // We now use the authenticated $userId to initialize the game settings.
                    $slotSettings = new SlotSettings($game, $userId);
                    if (!$slotSettings->is_active()) {
                        $response = '{"responseEvent":"error","responseType":"","serverResponse":"Game is disabled"}';
                        exit($response);
                    }

                    $postData = json_decode(trim(file_get_contents('php://input')), true);
                    $result_tmp = [];
                    if (isset($postData['gameData'])) {
                        $postData = $postData['gameData'];
                        $reqId = $postData['cmd'];
                        if (!isset($postData['cmd'])) {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"incorrect action"}';
                            exit($response);
                        }
                    } else {
                        $reqId = $postData['action'];
                    }
                    if ($reqId == 'SpinRequest') {
                        if ($postData['data']['coin'] <= 0 || $postData['data']['bet'] <= 0) {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid bet state"}';
                            exit($response);
                        }
                        if ($slotSettings->GetBalance() < ($postData['data']['coin'] * $postData['data']['bet'] * 20) && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= 0) {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid balance"}';
                            exit($response);
                        }
                    }
                    switch ($reqId) {
                        case 'InitRequest':
                            $result_tmp[0] = '{"action":"InitResponce","result":true,"sesId":"a40e5dc15a83a70f288e421fbcfc6de8","data":{"id":16183084}}';
                            exit($result_tmp[0]);
                            break;
                        case 'EventsRequest':
                            $result_tmp[0] = '{"action":"EventsResponce","result":true,"sesId":"a40e5dc15a83a70f288e421fbcfc6de8","data":[]}';
                            exit($result_tmp[0]);
                            break;
                        case 'APIVersionRequest':
                            $result_tmp[] = '{"action":"APIVersionResponse","result":true,"sesId":false,"data":{"router":"v3.12","transportConfig":{"reconnectTimeout":500000000000}}}';
                            break;
                        case 'PickBonusItemRequest':
                            $bonusSymbol = $postData['data']['index'];
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $bonusSymbol);
                            $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000217909","data":{"state":"PickBonus","params":{"picksRemain":"0","expandingSymbols":["' . $bonusSymbol . '"]}}}';
                            break;
                        case 'CheckBrokenGameRequest':
                            $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                            break;
                        case 'AuthRequest':
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                            $lastEvent = $slotSettings->GetHistory();
                            if ($lastEvent != 'NULL') {
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                $rp1 = implode(',', $lastEvent->serverResponse->reelsSymbols->rp);
                                $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                                $bet = $lastEvent->serverResponse->slotBet * 100 * 20;
                            } else {
                                $rp1 = implode(',', [
                                    rand(0, count($slotSettings->reelStrip1) - 3),
                                    rand(0, count($slotSettings->reelStrip2) - 3),
                                    rand(0, count($slotSettings->reelStrip3) - 3)
                                ]);
                                $rp_1 = rand(0, count($slotSettings->reelStrip1) - 3);
                                $rp_2 = rand(0, count($slotSettings->reelStrip2) - 3);
                                $rp_3 = rand(0, count($slotSettings->reelStrip3) - 3);
                                $rp_4 = rand(0, count($slotSettings->reelStrip4) - 3);
                                $rp_5 = rand(0, count($slotSettings->reelStrip5) - 3);
                                $rr1 = $slotSettings->reelStrip1[$rp_1];
                                $rr2 = $slotSettings->reelStrip2[$rp_2];
                                $rr3 = $slotSettings->reelStrip3[$rp_3];
                                $rr4 = $slotSettings->reelStrip4[$rp_4];
                                $rr5 = $slotSettings->reelStrip5[$rp_5];
                                $rp2 = '[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']';
                                $rr1 = $slotSettings->reelStrip1[$rp_1 + 1];
                                $rr2 = $slotSettings->reelStrip2[$rp_2 + 1];
                                $rr3 = $slotSettings->reelStrip3[$rp_3 + 1];
                                $rr3 = $slotSettings->reelStrip4[$rp_4 + 1];
                                $rr3 = $slotSettings->reelStrip5[$rp_5 + 1];
                                $rp2 .= (',[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']');
                                $rr1 = $slotSettings->reelStrip1[$rp_1 + 2];
                                $rr2 = $slotSettings->reelStrip2[$rp_2 + 2];
                                $rr3 = $slotSettings->reelStrip3[$rp_3 + 2];
                                $rr3 = $slotSettings->reelStrip4[$rp_4 + 2];
                                $rr3 = $slotSettings->reelStrip5[$rp_5 + 2];
                                $rp2 .= (',[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']');
                                $bet = $slotSettings->Bet[0] * 100 * 20;
                            }
                            if ($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame')) {
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            }
                            if ($slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames')) {
                                $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                $fRemain = $fTotal - $fCurrent;
                                $restoreString = ',"restoredGameCode":"340","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins","expandingSymbols":["1"]}';
                            }
                            $gambleHistory = $slotSettings->GetGameData($slotSettings->slotId . 'GambleHistory');
                            if (!is_array($gambleHistory)) {
                                $gambleHistory = [
                                    '"0"',
                                    '"1"',
                                    '"1"',
                                    '"1"',
                                    '"0"'
                                ];
                                $slotSettings->SetGameData($slotSettings->slotId . 'GambleHistory', $gambleHistory);
                            }
                            $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000292272","data":{"snivy":"proxy v6.10.48 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000292272","defaultLines":["0","1","2","3","4"],"bets":["2","3","4","5","10","15","20","30","40","50","100","200","300","400","800","1000"],"betMultiplier":"1.0000000","defaultBet":"2","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["1","1","1","1","1","1"],["0","0","0","0","0","0"],["2","2","2","2","2","2"],["0","1","2","2","1","0"],["2","1","0","0","1","2"]],"rtp":"0.00","payouts":[{"payout":"40","symbols":["0","0","0"],"type":"basic"},{"payout":"80","symbols":["0","0","0","0"],"type":"basic"},{"payout":"250","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"5000","symbols":["0","0","0","0","0","0"],"type":"basic"},{"payout":"20","symbols":["1","1","1"],"type":"basic"},{"payout":"40","symbols":["1","1","1","1"],"type":"basic"},{"payout":"100","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"600","symbols":["1","1","1","1","1","1"],"type":"basic"},{"payout":"10","symbols":["2","2","2"],"type":"basic"},{"payout":"20","symbols":["2","2","2","2"],"type":"basic"},{"payout":"50","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"300","symbols":["2","2","2","2","2","2"],"type":"basic"},{"payout":"5","symbols":["3","3","3"],"type":"basic"},{"payout":"10","symbols":["3","3","3","3"],"type":"basic"},{"payout":"25","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"100","symbols":["3","3","3","3","3","3"],"type":"basic"},{"payout":"5","symbols":["4","4","4"],"type":"basic"},{"payout":"10","symbols":["4","4","4","4"],"type":"basic"},{"payout":"25","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"100","symbols":["4","4","4","4","4","4"],"type":"basic"},{"payout":"5","symbols":["5","5","5"],"type":"basic"},{"payout":"10","symbols":["5","5","5","5"],"type":"basic"},{"payout":"25","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"100","symbols":["5","5","5","5","5","5"],"type":"basic"},{"payout":"5","symbols":["6","6","6"],"type":"basic"},{"payout":"10","symbols":["6","6","6","6"],"type":"basic"},{"payout":"25","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"100","symbols":["6","6","6","6","6","6"],"type":"basic"},{"payout":"3","symbols":["7","7","7","7"],"type":"scatter"},{"payout":"10","symbols":["7","7","7","7","7"],"type":"scatter"},{"payout":"200","symbols":["7","7","7","7","7","7"],"type":"scatter"}],"initialSymbols":[["6","3","3","5","6","4"],["6","8","3","0","2","4"],["8","6","3","1","7","4"]]},"jackpotsEnabled":"true","gameModes":"[]"}}';
                            break;
                        case 'BalanceRequest':
                            $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":"' . $slotSettings->slotCurrency . '"}}';
                            break;
                        case 'FreeSpinRequest':
                        case 'SpinRequest':
                            $postData['slotEvent'] = 'bet';
                            $bonusMpl = 1;
                            $linesId = [];
                            $linesId[0] = [
                                2,
                                2,
                                2,
                                2,
                                2,
                                2
                            ];
                            $linesId[1] = [
                                1,
                                1,
                                1,
                                1,
                                1,
                                1
                            ];
                            $linesId[2] = [
                                3,
                                3,
                                3,
                                3,
                                3,
                                3
                            ];
                            $linesId[3] = [
                                1,
                                2,
                                3,
                                3,
                                2,
                                1
                            ];
                            $linesId[4] = [
                                3,
                                2,
                                1,
                                1,
                                2,
                                3
                            ];
                            $lines = 5;
                            $betLine = $postData['data']['coin'] * $postData['data']['bet'];
                            $allbet = $betLine * $lines;
                            if (!isset($postData['slotEvent'])) {
                                $postData['slotEvent'] = 'bet';
                            }
                            if ($reqId == 'FreeSpinRequest') {
                                $postData['slotEvent'] = 'freespin';
                            }
                            if ($postData['slotEvent'] != 'freespin') {
                                $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $slotSettings->UpdateJackpots($allbet);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                            } else {
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                $bonusMpl = $slotSettings->slotFreeMpl;
                            }
                            $balance = sprintf('%01.2f', $slotSettings->GetBalance());
                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                            $winType = $winTypeTmp[0];
                            $spinWinLimit = $winTypeTmp[1];
                            for ($i = 0; $i <= 2000; $i++) {
                                $totalWin = 0;
                                $lineWins = [];
                                $cWins = [
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    0
                                ];
                                $wild = ['8'];
                                $scatter = '7';
                                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                $reelsTmp = $reels;
                                for ($k = 0; $k < $lines; $k++) {
                                    $tmpStringWin = '';
                                    for ($j = 0; $j < count($slotSettings->SymbolGame); $j++) {
                                        $csym = $slotSettings->SymbolGame[$j];
                                        if ($csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym])) {
                                        } else {
                                            $s = [];
                                            $s[0] = $reels['reel1'][$linesId[$k][0] - 1];
                                            $s[1] = $reels['reel2'][$linesId[$k][1] - 1];
                                            $s[2] = $reels['reel3'][$linesId[$k][2] - 1];
                                            $s[3] = $reels['reel4'][$linesId[$k][3] - 1];
                                            $s[4] = $reels['reel5'][$linesId[$k][4] - 1];
                                            $s[5] = $reels['reel5'][$linesId[$k][5] - 1];
                                            $p0 = $linesId[$k][0] - 1;
                                            $p1 = $linesId[$k][1] - 1;
                                            $p2 = $linesId[$k][2] - 1;
                                            $p3 = $linesId[$k][3] - 1;
                                            $p4 = $linesId[$k][4] - 1;
                                            $p5 = $linesId[$k][5] - 1;
                                            if ($s[0] == $csym || in_array($s[0], $wild)) {
                                                $mpl = 1;
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][1] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"]]}';
                                                }
                                            }
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild))) {
                                                $mpl = 1;
                                                if (in_array($s[0], $wild) && in_array($s[1], $wild)) {
                                                    $mpl = 0;
                                                } else if (in_array($s[0], $wild) || in_array($s[1], $wild)) {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"]]}';
                                                }
                                            }
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild))) {
                                                $mpl = 1;
                                                if (in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild)) {
                                                    $mpl = 0;
                                                } else if (in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild)) {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"]]}';
                                                }
                                            }
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild))) {
                                                $mpl = 1;
                                                if (in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild)) {
                                                    $mpl = 0;
                                                } else if (in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild)) {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"]]}';
                                                }
                                            }
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild))) {
                                                $mpl = 1;
                                                if (in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild)) {
                                                    $mpl = 0;
                                                } else if (in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild)) {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"]]}';
                                                }
                                            }
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[5] == $csym || in_array($s[5], $wild))) {
                                                $mpl = 1;
                                                if (in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) && in_array($s[5], $wild)) {
                                                    $mpl = 0;
                                                } else if (in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) || in_array($s[5], $wild)) {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][6] * $betLine * $mpl * $bonusMpl;
                                                if ($cWins[$k] < $tmpWin) {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                        }
                                    }
                                    if ($cWins[$k] > 0 && $tmpStringWin != '') {
                                        array_push($lineWins, $tmpStringWin);
                                        $totalWin += $cWins[$k];
                                    }
                                }
                                $scattersWin = 0;
                                $scattersWinB = 0;
                                $scattersPos = [];
                                $scattersStr = '';
                                $scattersCount = 0;
                                $bSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');
                                $bSymCnt = 0;
                                for ($r = 1; $r <= 6; $r++) {
                                    $isScat = false;
                                    for ($p = 0; $p <= 2; $p++) {
                                        if ($reels['reel' . $r][$p] == $scatter) {
                                            $scattersCount++;
                                            $scattersPos[] = '["' . ($r - 1) . '","' . $p . '"]';
                                            $isScat = true;
                                        }
                                    }
                                }
                                $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $lines * $bonusMpl;
                                $gameState = 'Ready';
                                if ($scattersCount >= 3 && $slotSettings->slotBonus) {
                                    $scw = '{"type":"WinAmount","amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                    array_push($lineWins, $scw);
                                } else if ($scattersCount >= 5 && $scattersWin > 0) {
                                    $scw = '{"wonSymbols":[' . implode(',', $scattersPos) . '],"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","type":"WinAmount"}';
                                    array_push($lineWins, $scw);
                                }
                                $totalWin += ($scattersWin + $scattersWinB);
                                if ($i > 1000) {
                                    $winType = 'none';
                                }
                                if ($i > 1500) {
                                    $response = '{"responseEvent":"error","responseType":"","serverResponse":"' . $totalWin . ' Bad Reel Strip"}';
                                    exit($response);
                                }
                                if ($slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom)) {
                                } else {
                                    $minWin = $slotSettings->GetRandomPay();
                                    if ($i > 700) {
                                        $minWin = 0;
                                    }
                                    if ($slotSettings->increaseRTP && $winType == 'win' && $totalWin < ($minWin * $allbet)) {
                                    } else {
                                        if ($i > 1500) {
                                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"Bad Reel Strip"}';
                                            exit($response);
                                        }
                                        if ($scattersCount >= 3 && $winType != 'bonus') {
                                        } else if ($totalWin <= $spinWinLimit && $winType == 'bonus') {
                                            $cBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                            if ($cBank < $spinWinLimit) {
                                                $spinWinLimit = $cBank;
                                            } else {
                                                break;
                                            }
                                        } else if ($totalWin > 0 && $totalWin <= $spinWinLimit && $winType == 'win') {
                                            $cBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                            if ($cBank < $spinWinLimit) {
                                                $spinWinLimit = $cBank;
                                            } else {
                                                break;
                                            }
                                        } else if ($totalWin == 0 && $winType == 'none') {
                                            break;
                                        }
                                    }
                                }
                            }
                            $flag = 0;
                            if ($totalWin > 0) {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin);
                                $flag = 6;
                            }
                            $reportWin = $totalWin;
                            if ($postData['slotEvent'] == 'freespin') {
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                            } else {
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                            }
                            $reels = $reelsTmp;
                            $jsSpin = '' . json_encode($reels) . '';
                            $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                            if ($totalWin > 0) {
                                $gambleHistory = $slotSettings->GetGameData($slotSettings->slotId . 'GambleHistory');
                                $winString = ',"slotWin":{"totalWin":"' . $totalWin . '","lineWinAmounts":[' . implode(',', $lineWins) . '],"canGamble":"true","gambleParams":{"history":[' . implode(',', $gambleHistory) . ']}}';
                            } else {
                                $winString = '';
                            }
                            $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                            $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '","' . $reels['reel6'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '","' . $reels['reel6'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '","' . $reels['reel6'][2] . '"]';
                            $slotSettings->SaveLogReport($response, $allbet, $lines, $reportWin, $postData['slotEvent']);
                            if ($postData['slotEvent'] == 'freespin') {
                                $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{"state":"FreeSpins"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                            } else {
                                $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000373695","data":{"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}' . $winString . ',"state":"' . $gameState . '"}}';
                            }
                            break;
                        case 'GambleRequest':
                            $Balance = $slotSettings->GetBalance();
                            $isGambleWin = rand(1, $slotSettings->GetGambleSettings());
                            $dealerCard = '';
                            $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                            $gambleWin = 0;
                            $statBet = $totalWin;
                            if ($slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom)) {
                                $isGambleWin = 0;
                            }
                            if ($slotSettings->GetBank('bonus') < ($totalWin * 2)) {
                                $isGambleWin = 0;
                            }
                            if ($isGambleWin == 1) {
                                $gambleState = 'true';
                                $gambleWin = $totalWin;
                                $totalWin = $totalWin * 2;
                            } else {
                                $gambleState = 'false';
                                $gambleWin = -1 * $totalWin;
                                $totalWin = 0;
                            }
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                            $slotSettings->SetBalance($gambleWin);
                            $slotSettings->SetBank('bonus', $gambleWin * -1);
                            $afterBalance = $slotSettings->GetBalance();
                            $jsSet = '{"dealerCard":"' . $dealerCard . '","gambleState":"' . $gambleState . '","totalWin":' . $totalWin . ',"afterBalance":' . $afterBalance . ',"Balance":' . $Balance . '}';
                            $response = '{"responseEvent":"gambleResult","serverResponse":' . $jsSet . '}';
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                            $slotSettings->SaveLogReport($response, $statBet, 1, $gambleWin, 'slotGamble');
                            $gambleHistory = $slotSettings->GetGameData($slotSettings->slotId . 'GambleHistory');
                            array_pop($gambleHistory);
                            array_unshift($gambleHistory, '"' . $postData['data']['card'] . '"');
                            $slotSettings->SetGameData($slotSettings->slotId . 'GambleHistory', $gambleHistory);
                            $result_tmp[0] = '{"action":"GambleResponse","result":"true","sesId":"10000528667","data":{"winning":"' . $totalWin . '","canGamble":"' . $gambleState . '"}}';
                            break;
                    }
                    $response = implode('------', $result_tmp);
                    $slotSettings->SaveGameData();
                    $slotSettings->SaveGameDataStatic();
                    echo ':::' . $response;
                } catch (\Exception $e) {
                    if (isset($slotSettings)) {
                        $slotSettings->InternalErrorSilent($e);
                    } else {
                        $strLog = '';
                        $strLog .= "\n";
                        $strLog .= ('{"responseEvent":"error","responseType":"' . $e . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
                        $strLog .= "\n";
                        $strLog .= ' ############################################### ';
                        $strLog .= "\n";
                        $slg = '';
                        if (file_exists(storage_path('logs/') . 'GameInternal.log')) {
                            $slg = file_get_contents(storage_path('logs/') . 'GameInternal.log');
                        }
                        file_put_contents(storage_path('logs/') . 'GameInternal.log', $slg . $strLog);
                    }
                }
            }, 5);
        }
    }
}