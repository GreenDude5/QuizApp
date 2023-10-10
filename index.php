<!DOCTYPE html>
<html>
<head>
    <title>Quiz Application</title>
</head>
<body>
    <h1>Quiz</h1>
    <form id="quiz-form">
        <div id="question-container">
            <p id="question"></p>
        </div>
        <div id="options-container">
        </div>
        <div id="status">Questions left: <span id="questions-left"></span></div>
        <button type="button" id="next-button">Next</button>
    </form>

    <script>
        var currentQuestion = 0;
        var questions = [];
        var correctAnswers = 0;

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

        var questionContainer = document.getElementById("question-container");
        var optionsContainer = document.getElementById("options-container");
        var questionElement = document.getElementById("question");
        var nextButton = document.getElementById("next-button");
        var questionsLeftElement = document.getElementById("questions-left");
        var statusElement = document.getElementById("status");

        function showQuestion() {
            var currentQ = questions[currentQuestion];
            questionElement.textContent = currentQ.question;
            optionsContainer.innerHTML = '';

            if (currentQ.type === 'closed') {
                for (var i = 0; i < currentQ.options.length; i++) {
                    var label = document.createElement('label');
                    label.innerHTML = `<input type="radio" name="answer" value="${i}"> ${currentQ.options[i]}`;
                    optionsContainer.appendChild(label);
                }
            } else if (currentQ.type === 'multiple') {
                for (var i = 0; i < currentQ.options.length; i++) {
                    var label = document.createElement('label');
                    label.innerHTML = `<input type="checkbox" name="answer" value="${i}"> ${currentQ.options[i]}`;
                    optionsContainer.appendChild(label);
                }
            } else if (currentQ.type === 'open') {
                var textarea = document.createElement('textarea');
                textarea.name = 'answer';
                optionsContainer.appendChild(textarea);
            }

            questionsLeftElement.textContent = questions.length - currentQuestion;
        }

        function checkAnswer() {
            var currentQ = questions[currentQuestion];
            if (currentQ.type !== 'open') {
                var answerElements = document.getElementsByName("answer");
                var selectedAnswers = [];
                for (var i = 0; i < answerElements.length; i++) {
                    if (answerElements[i].checked) {
                        selectedAnswers.push(parseInt(answerElements[i].value));
                    }
                }

                if (arraysEqual(selectedAnswers, currentQ.correctAnswers)) {
                    correctAnswers++;
                }
            }

            if (currentQuestion === questions.length - 1) {
                showResults();
            } else {
                currentQuestion++;
                showQuestion();
            }
        }

        function showResults() {
            questionContainer.style.display = 'none';
            statusElement.style.display = 'none';
            nextButton.style.display = 'none';
            var resultsElement = document.createElement('div');
            resultsElement.id = 'results';
            resultsElement.innerHTML = `Quiz finished! You answered ${correctAnswers} out of ${questions.length} questions correctly.`;
            document.body.appendChild(resultsElement);
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
