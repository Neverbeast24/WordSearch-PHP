<?php
// main.php

// Programming-related word list - all uppercase for consistency
$words = ["ALGORITHM", "JAVASCRIPT", "DATABASE", "FRAMEWORK", "DEBUGGING", "FUNCTION", "VARIABLE", "COMPILER", "RECURSION", "PYTHON"];

// Generate a 12x12 grid
$size = 12;
$grid = array_fill(0, $size, array_fill(0, $size, ''));

// All 8 directions:
// [row_change, col_change]
$directions = [
    [0, 1],   // Right
    [0, -1],  // Left (Reverse Horizontal)
    [1, 0],   // Down
    [-1, 0],  // Up (Reverse Vertical)
    [1, 1],   // Diagonal Down-Right
    [-1, -1], // Diagonal Up-Left (Reverse Diagonal Down-Right)
    [1, -1],  // Diagonal Down-Left
    [-1, 1],  // Diagonal Up-Right (Reverse Diagonal Down-Left)
];

// Place words randomly
$placedWords = []; // Track successfully placed words
$maxRetries = 10; // Maximum number of times to retry placing all words
$retryCount = 0;

while (count($placedWords) < count($words) && $retryCount < $maxRetries) {
    $retryCount++;
    $placedWords = [];
    $grid = array_fill(0, $size, array_fill(0, $size, '')); // Reset grid for retry
    
    foreach ($words as $word) {
        $placed = false;
        $len = strlen($word);
        $attempts = 0; // Limit attempts to prevent infinite loops on impossible grids
        while (!$placed && $attempts < 1000) { // Increased attempts for harder placement
            $attempts++;

            $dir_index = array_rand($directions);
            $dir = $directions[$dir_index];
            $dr = $dir[0]; // row change
            $dc = $dir[1]; // col change

            // Random starting position
            $startRow = rand(0, $size - 1);
            $startCol = rand(0, $size - 1);

            // Calculate potential end position
            $endRow = $startRow + $dr * ($len - 1);
            $endCol = $startCol + $dc * ($len - 1);

            // Check if word fits within grid boundaries
            if ($endRow < 0 || $endRow >= $size || $endCol < 0 || $endCol >= $size ||
                $startRow < 0 || $startRow >= $size || $startCol < 0 || $startCol >= $size) {
                continue; // Doesn't fit, try again
            }

            // Check for conflicts with existing letters
            $canPlace = true;
            for ($i = 0; $i < $len; $i++) {
                $r = $startRow + $dr * $i;
                $c = $startCol + $dc * $i;
                if ($grid[$r][$c] !== '' && $grid[$r][$c] !== $word[$i]) {
                    $canPlace = false; // Conflict found
                    break;
                }
            }

            if ($canPlace) {
                // Place the word
                for ($i = 0; $i < $len; $i++) {
                    $r = $startRow + $dr * $i;
                    $c = $startCol + $dc * $i;
                    $grid[$r][$c] = $word[$i];
                }
                $placed = true;
                $placedWords[] = $word; // Track this word as successfully placed
            }
        }
        // If a word couldn't be placed, break and retry the entire grid
        if (!$placed) {
            break;
        }
    }
}


// Fill empty cells with random letters
for ($i = 0; $i < $size; $i++) {
    for ($j = 0; $j < $size; $j++) {
        if ($grid[$i][$j] === '') {
            $grid[$i][$j] = chr(rand(65, 90)); // A-Z
        }
    }
}

// Get the current Unix timestamp when the page is loaded
$startTime = microtime(true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Programming Word Search</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; margin-top: 20px; background-color: #f4f4f4; }
        h2 { color: #333; }
        .game-container { display: flex; flex-direction: column; align-items: center; }
        #timer { font-size: 1.5em; margin-bottom: 15px; color: #555; font-weight: bold;}
        #score-display { font-size: 1.5em; margin-bottom: 15px; color: #555; font-weight: bold;}
        table { border-collapse: collapse; margin: 20px 0; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        td {
            width: 38px; height: 38px; text-align: center; font-size: 20px; font-weight: bold;
            border: 1px solid #ccc; cursor: pointer; user-select: none;
            transition: background-color 0.1s ease-in-out;
        }
        td.selected { background: #cce5ff; border-color: #007bff; } /* Light blue for selection */
        td.crossed { background: #d4edda; border-color: #28a745; text-decoration: none; color: #28a745; } /* Light green for found words */
        .word-list {
            list-style-type: none; padding: 0; margin-top: 20px; display: flex; flex-wrap: wrap; justify-content: center;
            max-width: 600px;
        }
        .word-list li {
            background-color: #e9ecef; padding: 8px 12px; margin: 5px; border-radius: 5px;
            font-weight: bold; color: #495057; transition: background-color 0.3s ease;
        }
        .word-list li.found {
            text-decoration: line-through; color: #28a745; background-color: #d4edda;
        }
        #congratulations {
            display: none; /* Hidden by default */
            margin-top: 30px;
            font-size: 2em;
            color: #28a745;
            font-weight: bold;
            animation: fadeIn 2s forwards;
            text-align: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .leaderboard-container {
            margin-top: 40px;
            width: 80%;
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .leaderboard-container h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .leaderboard-container ol {
            padding-left: 25px;
            margin: 0;
        }
        .leaderboard-container li {
            margin-bottom: 8px;
            font-size: 1.1em;
            color: #666;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #eee;
            padding-bottom: 5px;
        }
        .leaderboard-container li:last-child {
            border-bottom: none;
        }
        .leaderboard-container li span:first-child {
            font-weight: bold;
            color: #333;
        }
        .control-buttons {
            margin: 20px 0;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .control-buttons button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .control-buttons button:hover {
            background-color: #0056b3;
        }
        .control-buttons button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        #saveScoreBtn {
            background-color: #17a2b8 !important;
            font-weight: bold;
            border: 2px solid #138496;
        }
        #saveScoreBtn:hover:not(:disabled) {
            background-color: #138496 !important;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <h2>Programming Word Search</h2>
    <div class="game-container">
        <div id="timer">Time: 00:00</div>
        <div id="score-display">Score: 0</div>
        
        <div class="control-buttons">
            <button id="startAgainBtn" onclick="startNewGame()">Start Again</button>
            <button id="pauseBtn" onclick="togglePause()">Pause</button>
            <button id="saveScoreBtn" onclick="manualSaveScore()" style="display: none;">Save Score</button>
        </div>
        
        <table id="wordsearch">
            <?php for ($i = 0; $i < $size; $i++): ?>
                <tr>
                <?php for ($j = 0; $j < $size; $j++): ?>
                    <td data-row="<?= $i ?>" data-col="<?= $j ?>"><?= $grid[$i][$j] ?></td>
                <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
        <ul class="word-list" id="wordList">
            <?php foreach ($placedWords as $w): ?>
                <li data-word="<?= $w ?>"><?= $w ?></li>
            <?php endforeach; ?>
        </ul>

        <div id="congratulations"></div>
    </div>

    <div class="leaderboard-container">
        <h3>Leaderboard</h3>
        <ol id="leaderboardList">
            <li>Loading leaderboard...</li>
        </ol>
    </div>

    <script>
        let selecting = false;
        let startCell = null;
        let endCell = null;
        const table = document.getElementById('wordsearch');
        const wordListItems = document.getElementById('wordList').getElementsByTagName('li');
        const congratulationMessage = document.getElementById('congratulations');
        const timerDisplay = document.getElementById('timer');
        const scoreDisplay = document.getElementById('score-display');
        const leaderboardList = document.getElementById('leaderboardList');

        let currentSelectedCells = [];
        let foundWordsCount = 0;
        const totalWords = <?= count($placedWords) ?>; // Use actually placed words count
        const gridSize = <?= $size ?>; // Pass PHP grid size to JavaScript
        let score = 0;
        let startTime = <?= $startTime ?>; // PHP's microtime(true) passed to JS
        let timerInterval;
        let gameFinished = false;
        let gamePaused = false;
        let finalGameScore = 0;
        let finalGameTime = '00:00';

        const WORD_SCORE = 100; // Points for each word
        const TIME_BONUS_PER_SECOND = 5; // Points per second remaining in a hypothetical max time

        // Debug function
        function debugLog(message) {
            console.log('Word Search Debug:', message);
        }
        
        // Log which words were actually placed in the grid
        debugLog('Words placed in grid: <?= implode(", ", $placedWords) ?>');
        debugLog('Total words to find: ' + totalWords);

        function startTimer() {
            timerInterval = setInterval(() => {
                if (!gameFinished && !gamePaused) {
                    // Use performance.now() for more accurate client-side timing relative to page load
                    const elapsedTime = (performance.now() / 1000);
                    const minutes = Math.floor(elapsedTime / 60);
                    const seconds = Math.floor(elapsedTime % 60);
                    timerDisplay.textContent = `Time: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }
            }, 1000); // Update every second
        }

        function stopTimer() {
            clearInterval(timerInterval);
        }

        function updateScore(points) {
            score += points;
            scoreDisplay.textContent = `Score: ${score}`;
        }

        function startNewGame() {
            // Always allow starting a new game, regardless of save status
            if (!gameFinished && (foundWordsCount > 0 || score > 0)) {
                const confirmed = confirm("Are you sure you want to start a new game? Your current progress will be lost.");
                if (!confirmed) {
                    return;
                }
            }
            
            // Hide the save button when starting new game
            document.getElementById('saveScoreBtn').style.display = 'none';
            
            // Reload the page to start a new game
            window.location.reload();
        }

        function togglePause() {
            const pauseBtn = document.getElementById('pauseBtn');
            if (gameFinished) return; // Can't pause if game is finished
            
            gamePaused = !gamePaused;
            if (gamePaused) {
                pauseBtn.textContent = 'Resume';
                pauseBtn.style.backgroundColor = '#28a745'; // Green for resume
                // Disable game interactions
                table.style.pointerEvents = 'none';
            } else {
                pauseBtn.textContent = 'Pause';
                pauseBtn.style.backgroundColor = '#007bff'; // Blue for pause
                // Enable game interactions
                table.style.pointerEvents = 'auto';
            }
        }

        async function manualSaveScore() {
            if (!gameFinished) {
                alert("⚠️ Please finish the game first before saving your score!");
                return;
            }

            const playerName = prompt("🎯 Enter your name for the leaderboard (max 15 chars):");
            if (!playerName || !playerName.trim()) {
                alert("❌ Please enter a valid name to save your score.");
                return;
            }

            const trimmedName = playerName.trim().substring(0, 15);
            const saveBtn = document.getElementById('saveScoreBtn');
            
            // Disable button during save
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            
            console.log('🔵 Manual save triggered:', trimmedName, finalGameScore, finalGameTime);
            
            try {
                const success = await saveScore(trimmedName, finalGameScore, finalGameTime);
                
                if (success) {
                    alert("✅ Score saved successfully! Check the leaderboard below.");
                    saveBtn.textContent = 'Score Saved!';
                    saveBtn.style.backgroundColor = '#28a745'; // Green
                    setTimeout(() => {
                        saveBtn.style.display = 'none'; // Hide after successful save
                    }, 2000);
                } else {
                    alert("❌ Failed to save score. Please try again.");
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Score';
                }
            } catch (error) {
                console.error('Manual save error:', error);
                alert("❌ Error saving score: " + error.message);
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Score';
            }
        }

        // Function to clear only the 'selected' class from all cells
        function clearSelectionHighlights() {
            document.querySelectorAll('td.selected').forEach(cell => cell.classList.remove('selected'));
        }

        table.addEventListener('mousedown', function(e) {
            if (gameFinished || gamePaused) return; // Prevent interaction after game ends or when paused

            if (e.target.tagName === 'TD') {
                e.preventDefault(); // Prevent text selection
                selecting = true;
                clearSelectionHighlights(); // Clear any existing highlight when starting a new selection
                startCell = e.target;
                startCell.classList.add('selected');
                currentSelectedCells = [startCell]; // Initialize with the start cell
                endCell = startCell; // End cell starts as the start cell
                debugLog('Mouse down on cell: ' + startCell.dataset.row + ',' + startCell.dataset.col);
            }
        });

        table.addEventListener('mouseover', function(e) {
            if (gameFinished || gamePaused) return; // Prevent interaction after game ends or when paused
            if (!selecting) return; // Only process mouseover if a selection is active

            if (e.target.tagName === 'TD') {
                const hoveredCell = e.target;
                debugLog('Mouse over cell: ' + hoveredCell.dataset.row + ',' + hoveredCell.dataset.col);
                const potentialCells = getCellsBetween(startCell, hoveredCell, gridSize, table); // Pass gridSize and table
                
                debugLog('Potential cells count: ' + potentialCells.length);
                if (potentialCells.length > 0) { // If a valid line can be formed
                    clearSelectionHighlights(); // Clear previous temporary highlights
                    currentSelectedCells = potentialCells;
                    currentSelectedCells.forEach(cell => cell.classList.add('selected'));
                    endCell = hoveredCell; // Update endCell to the current valid hovered cell
                } else {
                    // If no valid line, ensure only the startCell remains highlighted
                    clearSelectionHighlights();
                    if (startCell) {
                        startCell.classList.add('selected');
                        currentSelectedCells = [startCell];
                        endCell = startCell; // Reset endCell if the path becomes invalid
                    }
                }
            }
        });

        document.addEventListener('mouseup', function(e) {
            if (gameFinished || gamePaused) return; // Prevent interaction after game ends or when paused

            if (selecting && startCell && endCell) { // Ensure a valid selection was attempted
                // Get the word from the highlighted cells
                const wordChars = currentSelectedCells.map(cell => cell.textContent).join('');
                const revWordChars = currentSelectedCells.map(cell => cell.textContent).reverse().join('');

                let found = false;
                for (let i = 0; i < wordListItems.length; i++) {
                    const listItem = wordListItems[i];
                    // Skip if the word is already found
                    if (listItem.classList.contains('found')) {
                        continue;
                    }

                    const targetWord = listItem.textContent;

                    if (targetWord === wordChars || targetWord === revWordChars) {
                        // Word found!
                        currentSelectedCells.forEach(cell => {
                            cell.classList.remove('selected'); // Remove temporary selection highlight
                            cell.classList.add('crossed');      // Apply permanent 'crossed' highlight
                        });
                        listItem.classList.add('found'); // Mark word in the list as found
                        foundWordsCount++;
                        updateScore(WORD_SCORE); // Add points for finding a word
                        found = true;
                        break; // Exit loop, word found
                    }
                }

                if (!found) {
                    // If the selected word was NOT found, simply clear the 'selected' highlight
                    clearSelectionHighlights();
                }

                // Check for game completion
                if (foundWordsCount === totalWords && !gameFinished) {
                    gameFinished = true;
                    stopTimer();
                    
                    // Disable pause button and enable start again
                    document.getElementById('pauseBtn').disabled = true;
                    const startBtn = document.getElementById('startAgainBtn');
                    startBtn.disabled = false;
                    startBtn.textContent = 'Play Again';
                    startBtn.style.backgroundColor = '#28a745'; // Green

                    // Calculate final time and score
                    const finalElapsedTimeSeconds = (performance.now() / 1000);
                    const finalTimeFormatted = timerDisplay.textContent.replace('Time: ', '');

                    // Add time bonus
                    const MAX_TIME_FOR_BONUS = 300; // seconds
                    const timeRemainingForBonus = Math.max(0, MAX_TIME_FOR_BONUS - finalElapsedTimeSeconds);
                    const timeBonus = Math.floor(timeRemainingForBonus * TIME_BONUS_PER_SECOND);
                    updateScore(timeBonus);

                    // Store final results for manual saving
                    finalGameScore = score;
                    finalGameTime = finalTimeFormatted;

                    congratulationMessage.innerHTML = `
                        <h2>Congratulations!</h2>
                        <p>You found all the programming terms in ${finalTimeFormatted}!</p>
                        <p>Your final score is: <strong style="color: #007bff;">${finalGameScore}</strong></p>
                        <p><small>Click "Save Score" button above to save to leaderboard, or "Play Again" to start over!</small></p>
                    `;
                    congratulationMessage.style.display = 'block';

                    // Show the save button
                    const saveBtn = document.getElementById('saveScoreBtn');
                    saveBtn.style.display = 'inline-block';
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Score';

                    console.log('🎮 Game completed! Final score stored:', finalGameScore, finalGameTime);
                }
            }

            // Always reset selection state regardless of whether a word was found or not
            selecting = false;
            startCell = null;
            endCell = null;
            currentSelectedCells = [];
        });

        // This is the CRITICAL function that has been refined
        function getCellsBetween(cell1, cell2, gridSize, gameTable) {
            if (!cell1 || !cell2) return [];

            const r1 = parseInt(cell1.dataset.row), c1 = parseInt(cell1.dataset.col);
            const r2 = parseInt(cell2.dataset.row), c2 = parseInt(cell2.dataset.col);
            let cells = [];

            const dr = r2 - r1; // Delta row
            const dc = c2 - c1; // Delta col

            // Determine if it's a straight line (horizontal, vertical, or 45-degree diagonal)
            const isHorizontal = dr === 0 && dc !== 0;
            const isVertical = dc === 0 && dr !== 0;
            // Check for diagonal (absolute difference in rows equals absolute difference in columns)
            const isDiagonal = Math.abs(dr) === Math.abs(dc) && dr !== 0;
            const isSingleCell = dr === 0 && dc === 0;

            if (isSingleCell) {
                cells.push(cell1);
                return cells;
            }

            if (isHorizontal || isVertical || isDiagonal) {
                const len = Math.max(Math.abs(dr), Math.abs(dc)) + 1; // Number of cells including start and end
                const stepR = dr === 0 ? 0 : (dr > 0 ? 1 : -1); // +1 for down, -1 for up, 0 for horizontal
                const stepC = dc === 0 ? 0 : (dc > 0 ? 1 : -1); // +1 for right, -1 for left, 0 for vertical

                for (let i = 0; i < len; i++) {
                    const currentRow = r1 + i * stepR;
                    const currentCol = c1 + i * stepC;

                    // Ensure the cell is within bounds before adding
                    if (currentRow >= 0 && currentRow < gridSize && currentCol >= 0 && currentCol < gridSize) {
                        // Access cell using table.rows[row].cells[col] for robustness
                        cells.push(gameTable.rows[currentRow].cells[currentCol]);
                    } else {
                        // If any cell in the proposed line is out of bounds, this is not a valid selection
                        return [];
                    }
                }
            }
            return cells;
        }

        async function saveScore(playerName, score, time) {
            console.log('=== SAVE SCORE DEBUG START ===');
            console.log('Input parameters:', {playerName, score, time});
            
            try {
                const requestData = { name: playerName, score: score, time: time };
                console.log('Request data:', requestData);
                
                const response = await fetch('save_score.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData),
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    console.error('Network error:', response.status);
                    return false;
                }
                
                const responseText = await response.text();
                console.log('Raw response text:', responseText);
                
                const result = JSON.parse(responseText);
                console.log('Parsed result:', result);
                
                if (result.success) {
                    console.log('✅ Save successful!');
                    // Force reload leaderboard
                    await loadLeaderboard();
                    console.log('✅ Leaderboard reloaded');
                    return true;
                } else {
                    console.error('❌ Save failed:', result.message);
                    return false;
                }
            } catch (error) {
                console.error('❌ Exception in saveScore:', error);
                return false;
            } finally {
                console.log('=== SAVE SCORE DEBUG END ===');
            }
        }

        async function loadLeaderboard() {
            try {
                debugLog('Loading leaderboard...');
                const response = await fetch('leaderboard.json?' + new Date().getTime()); // Cache busting
                debugLog('Leaderboard response status: ' + response.status);
                const scores = await response.json();
                debugLog('Leaderboard scores loaded: ' + scores.length + ' entries');

                leaderboardList.innerHTML = ''; // Clear existing list
                if (scores.length === 0) {
                    leaderboardList.innerHTML = '<li>No scores yet.</li>';
                    return;
                }

                // Sort scores by highest score first, then by fastest time if scores are equal
                scores.sort((a, b) => {
                    if (b.score !== a.score) {
                        return b.score - a.score;
                    }
                    // If scores are equal, sort by time (assuming "MM:SS" format for simplicity)
                    // Convert time to seconds for comparison
                    const timeToSeconds = (timeStr) => {
                        const parts = timeStr.split(':');
                        if (parts.length !== 2) return Infinity; // Handle invalid format
                        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
                    };
                    const timeA = timeToSeconds(a.time);
                    const timeB = timeToSeconds(b.time);
                    return timeA - timeB; // Lower time is better
                });

                // Display top 10 scores
                scores.slice(0, 10).forEach((entry, index) => {
                    const li = document.createElement('li');
                    li.innerHTML = `<span>${entry.name}</span> <span>${entry.score} pts (${entry.time})</span>`;
                    leaderboardList.appendChild(li);
                });

            } catch (error) {
                console.error("Error loading leaderboard:", error);
                debugLog('Error loading leaderboard: ' + error.message);
                leaderboardList.innerHTML = '<li>Error loading leaderboard.</li>';
            }
        }

        // Initialize game on page load
        document.addEventListener('DOMContentLoaded', () => {
            startTimer();
            loadLeaderboard();
        });
    </script>
</body>
</html>