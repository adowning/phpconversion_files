# TypeScript Facade Server API Documentation

## Base URL

The base URL for the API is not fixed and depends on the deployment environment.
The following endpoint is relative to the facade server's base URL.

## Game Spin API

### `POST /api/v1/games/:gameName/spin`

This endpoint processes a game spin request for the specified `gameName`.

**URL Parameters:**

*   `:gameName` (string, required): The unique name of the game to play (e.g., "NarcosNET", "DiscoFruitsNG").

**Request Body (JSON):**

The request body should be a JSON object with the following properties:

*   `userId` (string, required): The unique identifier for the user initiating the spin.
*   **For NarcosNET-style games:**
    *   `bet_betlevel` (number, optional): The bet level for the spin. Defaults to a game-specific default if not provided (e.g., 1).
    *   `bet_denomination` (number, optional): The coin denomination for the spin. Defaults to the game's primary denomination if not provided.
*   **For DiscoFruitsNG-style games:**
    *   `coin` (number, optional): The coin value for the bet. Defaults to game's denomination or a standard value.
    *   `bet` (number, optional): The bet multiplier or line bet. Defaults to a game-specific default.

**Example Request (NarcosNET):**

```json
{
  "userId": "user-uuid-12345",
  "bet_betlevel": 2,
  "bet_denomination": 0.01
}
```

**Example Request (DiscoFruitsNG):**
```json
{
  "userId": "user-uuid-67890",
  "coin": 0.05,
  "bet": 5
}
```

**Success Response (200 OK):**

A JSON object with the following properties:

*   `message` (string): A success message (e.g., "Spin successful for game 'NarcosNET'.").
*   `newBalance` (number): The user's updated balance after the spin.
*   `totalWin` (number): The total amount won in this spin.
*   `reels` (object/array): The final reel symbols or configuration to be displayed by the client. The structure of this object is determined by the specific PHP game engine.
*   `updatedGameData` (object): The updated session data for the user and game (e.g., free spin counts, bonus states). The structure is determined by the PHP game engine.

**Example Success Response:**

```json
{
  "message": "Spin successful for game 'NarcosNET'.",
  "newBalance": 985.50,
  "totalWin": 15.00,
  "reels": { /* ... reel data from PHP engine ... */ },
  "updatedGameData": { /* ... new session data from PHP engine ... */ }
}
```

**Error Responses:**

*   **400 Bad Request:**
    *   If `userId` is missing.
      ```json
      { "error": "Missing userId in request.", "errorId": "<request_id>" }
      ```
    *   If `gameName` is not supported (though this might be caught before detailed processing).
      ```json
      { "error": "Game '<gameName>' is not supported for spin action.", "errorId": "<request_id>" }
      ```
    *   If user has no `shop_id`.
       ```json
      { "error": "User <userId> does not have a shop_id.", "errorId": "<request_id>" }
      ```
*   **404 Not Found:**
    *   If the specified `userId` is not found.
      ```json
      { "error": "User with ID <userId> not found.", "errorId": "<request_id>" }
      ```
    *   If the specified `gameName` is not found in the database.
      ```json
      { "error": "Game '<gameName>' not found.", "errorId": "<request_id>" }
      ```
    *   If the user's `shop_id` does not correspond to a valid shop.
      ```json
      { "error": "Shop with ID <shopId> not found.", "errorId": "<request_id>" }
      ```
*   **500 Internal Server Error:**
    *   For general database errors during data fetching or persistence.
      ```json
      { "error": "Database error during data fetching.", "errorId": "<request_id>" }
      ```
      ```json
      { "error": "Error saving spin results.", "errorId": "<request_id>" }
      ```
    *   If the PHP game engine returns an invalid or incomplete response.
      ```json
      { "error": "Invalid response from game engine.", "errorId": "<request_id>" }
      ```
    *   If there's an error calling the PHP game engine (network issue, PHP error).
      ```json
      { "error": "Error from PHP engine.", "errorId": "<request_id>", "details": { /* optional PHP error data */ } }
      ```
    *   For any other unhandled server-side errors.
      ```json
      { "error": "Something broke unexpectedly!", "errorId": "<request_id>" }
      ```
      ```json
      { "error": "Something broke!", "errorId": "<request_id>" } // From global fallback
      ```

**Notes:**

*   The `errorId` field in error responses corresponds to the request ID logged by the server, which can be used for tracing issues.
*   The internal communication with PHP engines (`http://localhost:8080/<GameName>/index.php` by default) should be on a private network and not exposed externally. The base URL for PHP engines is configurable via the `PHP_ENGINE_BASE_URL` environment variable.
*   User authentication and authorization are assumed to be handled by upstream middleware or by verifying a token that provides the `userId`. The current implementation expects `userId` in the request body.
