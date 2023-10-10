<!DOCTYPE html>
<html>
<head>
    <title>Quiz Application</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Quiz</h1>
    <form id="quiz-form">
        <div id="question-container">
            <p id="question"></p>
        </div>
        <div id="options-container">
        </div>
        <div id="status" class="hidden">Questions left: <span id="questions-left"></span></div>
        <div id="open-question-container" class="hidden">
            <label for="open-answer">Your Answer:</label>
            <textarea id="open-answer" name="open-answer"></textarea>
        </div>
        <button type="button" id="next-button">Next</button>
        <button type="button" id="start-new-quiz">Start New Quiz</button>
    </form>
    <div id="results" class="hidden"></div>

    <script>
        var questions = [];
        var selectedQuestions = [];
        var numberOfQuestions = 5; // Change this to the desired number of questions

        <?php
            $lines = file("quiz_data.txt", FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                $data = explode("; ", $line);
                $questionType = $data[0];
                $questionContent = $data[1];

                if ($questionType === "closed") {
                    $options = array_slice($data, 2, 4);
                    $correctAnswerIndex = intval($data[6]);
                    echo "questions.push({type: 'closed', question: '$questionContent', options: ['" . implode("', '", $options) . "'], correctAnswer: $correctAnswerIndex});\n";
                } elseif ($questionType === "multiple") {
                    $options = array_slice($data, 2, 4);
                    $correctAnswerIndices = array_map('intval', explode(',', $data[6]));
                    echo "questions.push({type: 'multiple', question: '$questionContent', options: ['" . implode("', '", $options) . "'], correctAnswers: [" . implode(", ", $correctAnswerIndices) . "]});\n";
                } elseif ($questionType === "open") {
                    echo "questions.push({type: 'open', question: '$questionContent'});\n";
                }
            }
        ?>

        var startNewQuizButton = document.getElementById("start-new-quiz");
        var questionContainer = document.getElementById("question-container");
        var optionsContainer = document.getElementById("options-container");
        var openQuestionContainer = document.getElementById("open-question-container");
        var questionElement = document.getElementById("question");
        var nextButton = document.getElementById("next-button");
        var questionsLeftElement = document.getElementById("questions-left");
        var openAnswers = [];
        var currentQuestion = 0;
        var correctAnswers = 0;

        // Initially hide the "Results" section
        var resultsElement = document.getElementById("results");
        resultsElement.style.display = "none";

        startNewQuizButton.addEventListener("click", function() {
            startNewQuiz();
        });

        function startNewQuiz() {
            // Reset quiz data and show the quiz form
            selectedQuestions = [];
            currentQuestion = 0;
            correctAnswers = 0;
            openAnswers = [];
            
            // Randomly select questions for the new quiz
            while (selectedQuestions.length < numberOfQuestions && questions.length > 0) {
                var randomIndex = Math.floor(Math.random() * questions.length);
                selectedQuestions.push(questions.splice(randomIndex, 1)[0]);
            }
            
            showQuestion();
            document.getElementById("quiz-form").style.display = "block";
            resultsElement.style.display = "none"; // Hide the "Results" section
            startNewQuizButton.style.display = "none";
            document.getElementById("status").classList.remove("hidden");
        }

        function showQuestion() {
            if (currentQuestion < selectedQuestions.length) {
                var currentQ = selectedQuestions[currentQuestion];
                questionElement.textContent = currentQ.question;
                optionsContainer.innerHTML = '';
    
                if (currentQ.type === 'closed') {
                    for (var i = 0; i < currentQ.options.length; i++) {
                        var label = document.createElement('label');
                        label.innerHTML = `<input type="radio" name="answer" value="${i}"> ${currentQ.options[i]}`;
                        optionsContainer.appendChild(label);
                    }
                    openQuestionContainer.classList.add("hidden");
                } else if (currentQ.type === 'multiple') {
                    for (var i = 0; i < currentQ.options.length; i++) {
                        var label = document.createElement('label');
                        label.innerHTML = `<input type="checkbox" name="answer" value="${i}"> ${currentQ.options[i]}`;
                        optionsContainer.appendChild(label);
                    }
                    openQuestionContainer.classList.add("hidden");
                } else if (currentQ.type === 'open') {
                    openQuestionContainer.classList.remove("hidden");
                }
    
                questionsLeftElement.textContent = numberOfQuestions - currentQuestion;
                nextButton.style.display = 'block'; // Display the "Next" button
            } else {
                // No more questions left, hide the "Next" button and display the "Results" button
                nextButton.style.display = 'none';
                resultsElement.style.display = "block"; // Display the "Results" section
            }
        }

        function checkAnswer() {
            if (currentQuestion < selectedQuestions.length) {
                var currentQ = selectedQuestions[currentQuestion];
                if (currentQ.type !== 'open') {
                    var answerElements = document.getElementsByName("answer");
                    var selectedAnswers = [];
                    for (var i = 0; i < answerElements.length; i++) {
                        if (answerElements[i].checked) {
                            selectedAnswers.push(parseInt(answerElements[i].value));
                        }
                    }
    
                    if (arraysEqual(selectedAnswers, currentQ.correctAnswers)) {
                        // Increment correctAnswers only for non-open questions
                        correctAnswers++;
                    }
                } else {
                    // Store the user's answer for open questions
                    openAnswers.push(document.getElementById("open-answer").value);
                }
    
                if (currentQuestion === numberOfQuestions - 1) {
                    // No more questions left, display the "Results" button
                    showResults();
                } else {
                    currentQuestion++;
                    showQuestion();
                }
            }
        }

        function showResults() {
            var resultsElement = document.getElementById("results");
            resultsElement.innerHTML = '';
            resultsElement.classList.remove("hidden");
            
            var resultText = `Quiz finished! You answered ${correctAnswers} out of ${numberOfQuestions - openAnswers.length} non-open questions correctly.`;
            if (openAnswers.length > 0) {
                resultText += "<br><br>Open questions and your answers:<br>";
                for (var i = 0; i < openAnswers.length; i++) {
                    resultText += `<strong>Question ${i + 1}:</strong><br>${selectedQuestions[i].question}<br><em>Your Answer:</em> ${openAnswers[i]}<br><br>`;
                }
            }
            resultsElement.innerHTML = resultText;
            startNewQuizButton.style.display = "block"; // Show "Start New Quiz" button
        }

        function arraysEqual(arr1, arr2) {
            if (arr1 === undefined || arr2 === undefined) {
                return false;
            }
            if (arr1.length !== arr2.length) return false;
            for (var i = 0; i < arr1.length; i++) {
                if (arr1[i] !== arr2[i]) return false;
            }
            return true;
        }

        nextButton.addEventListener("click", function() {
            checkAnswer();
        });

        showQuestion();
    </script>
</body>
</html>
