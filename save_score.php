<?php
// save_score.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors directly, but log them

// Handle AJAX submission
header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the received data for debugging
error_log("Save Score Debug - Received data: " . $input);

// Validate input
if (!isset($data['name']) || !isset($data['score']) || !isset($data['time'])) {
    error_log("Save Score Error - Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit();
}

$playerName = htmlspecialchars(trim(substr($data['name'], 0, 15))); // Sanitize and limit name to 15 chars
$playerScore = (int)$data['score'];
$playerTime = htmlspecialchars(trim($data['time'])); // Sanitize time string

// Basic validation for name and score
if (empty($playerName) || $playerScore < 0 || !preg_match('/^\d{2}:\d{2}$/', $playerTime)) {
    error_log("Save Score Error - Validation failed: name='$playerName', score=$playerScore, time='$playerTime'");
    echo json_encode(['success' => false, 'message' => 'Invalid name, score, or time format.']);
    exit();
}

$result = saveScoreToFile($playerName, $playerScore, $playerTime);
echo json_encode($result);

// Function to save score to file
function saveScoreToFile($playerName, $playerScore, $playerTime) {
    $leaderboardFile = 'leaderboard.json';

    // Ensure the file exists and is writable
    if (!file_exists($leaderboardFile)) {
        // Attempt to create the file if it doesn't exist
        file_put_contents($leaderboardFile, '[]');
        if (!file_exists($leaderboardFile)) {
            error_log("Save Score Error - Cannot create leaderboard file");
            return ['success' => false, 'message' => 'Leaderboard file does not exist and could not be created.'];
        }
    }

    // Get existing scores
    $currentScores = [];
    if (file_get_contents($leaderboardFile) !== false) {
        $fileContent = file_get_contents($leaderboardFile);
        if ($fileContent !== '') {
            $decoded = json_decode($fileContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $currentScores = $decoded;
            }
        }
    }

    // Add new score
    $newEntry = [
        'name' => $playerName,
        'score' => $playerScore,
        'time' => $playerTime,
        'timestamp' => time()
    ];
    $currentScores[] = $newEntry;

    // Save back to file
    if (file_put_contents($leaderboardFile, json_encode($currentScores, JSON_PRETTY_PRINT))) {
        error_log("Save Score Success - Score saved for: $playerName");
        return ['success' => true, 'message' => 'Score saved successfully!'];
    } else {
        error_log("Save Score Error - Failed to write to file: $leaderboardFile");
        return ['success' => false, 'message' => 'Failed to write score to file.'];
    }
}
?>