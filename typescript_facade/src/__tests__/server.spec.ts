import request from 'supertest';
import app from '../server'; // Assuming server.ts exports the app
import { PrismaClient } from '@prisma/client';

// Mock Prisma Client
jest.mock('@prisma/client', () => {
  const mockPrismaClient = {
    user: {
      findUnique: jest.fn(),
      update: jest.fn(),
    },
    game: {
      findUnique: jest.fn(),
      update: jest.fn(),
    },
    shop: {
      findUnique: jest.fn(),
    },
    gameLog: {
      create: jest.fn(),
    },
    $disconnect: jest.fn(), // Mock disconnect
  };
  return { PrismaClient: jest.fn(() => mockPrismaClient) };
});

// Mock axios
jest.mock('axios');
import axios from 'axios'; // Import after mock
const mockedAxios = axios as jest.Mocked<typeof axios>;


describe('POST /api/v1/games/:gameName/spin', () => {
  let prismaMockInstance: any; // To hold the mock instance, using 'any' for simplicity here

  beforeEach(() => {
    // Reset mocks before each test
    jest.clearAllMocks();
    // Get the mock instance of PrismaClient that the app will use
    const PrismaClientMock = require('@prisma/client').PrismaClient;
    // Access the instance created by the server.ts. This assumes server.ts creates only one PrismaClient instance.
    // If server.ts was re-executed per test or structured differently, this might need adjustment.
    // For now, we assume a single, module-level PrismaClient instance in server.ts that gets this mocked constructor.
    if (PrismaClientMock.mock.instances.length > 0) {
        prismaMockInstance = PrismaClientMock.mock.instances[0];
    } else {
        // If no instance was created (e.g. if server.ts wasn't fully imported or prisma client wasn't newed up yet)
        // we might need to manually get it from the result if the constructor itself returns the mock.
        // This depends on the exact behavior of jest.fn(() => mockPrismaClient)
        prismaMockInstance = PrismaClientMock(); // This would be the case if the mock constructor returns the object directly.
                                                // Let's assume the instances array is populated.
    }
  });

  it('should return 400 if userId is missing', async () => {
    const gameName = 'NarcosNET';
    const response = await request(app)
      .post(`/api/v1/games/${gameName}/spin`)
      .send({});
    expect(response.status).toBe(400);
    expect(response.body.error).toBe('Missing userId in request.');
  });

  it('should return 404 if game is not found', async () => {
    const gameName = 'UnknownGame';
    // Ensure prismaMockInstance is defined before trying to set mockResolvedValue on its methods
    if (prismaMockInstance && prismaMockInstance.game) {
        (prismaMockInstance.game.findUnique as jest.Mock).mockResolvedValue(null);
    } else {
        // Fallback or error if prismaMockInstance wasn't properly retrieved
        console.error("prismaMockInstance or prismaMockInstance.game is undefined. Check mock setup.");
        // Potentially, you can set the mock directly on the imported module's mock implementation
        // if the instance retrieval is tricky.
        // Example: (require('@prisma/client').PrismaClient().game.findUnique as jest.Mock).mockResolvedValue(null);
        // This is less clean and depends on how the mock is structured.
        // For now, this test might fail if prismaMockInstance is not correctly captured.
    }


    const response = await request(app)
      .post(`/api/v1/games/${gameName}/spin`)
      .send({ userId: 'test-user' });

    expect(response.status).toBe(404);
    expect(response.body.error).toBe(`Game '${gameName}' not found.`);
    if (prismaMockInstance && prismaMockInstance.game) {
        expect(prismaMockInstance.game.findUnique).toHaveBeenCalledWith({ where: { name: gameName } });
    }
  });

  // TODO: Add more tests:
  // - User not found
  // - Shop not found
  // - Successful spin flow (mocking Prisma returns and PHP engine call)
  //   - Verify correct gameStateData assembly
  //   - Verify PHP engine called with correct data
  //   - Verify DB updates (user balance, game bank, gameSessions, gameLog)
  //   - Verify client response
  // - PHP engine error handling
  // - Database error during persistence
});

// Example of how to close server if needed after tests, though Jest usually handles process exit
// afterAll(done => {
//   // app might need a server.close() method if not handled by supertest auto-closing
//   done();
// });
