import { useState, useEffect } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

const QuizRenderer = ({ quizId }) => {
	const [quiz, setQuiz] = useState(null);
	const [answers, setAnswers] = useState([]);
	const [results, setResults] = useState(null);
	const [error, setError] = useState(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		loadQuiz();
	}, [quizId]);

	const loadQuiz = async () => {
		try {
			const response = await apiFetch({
				path: `/quiz-plugin/v1/quiz/${quizId}`,
			});
			setQuiz(response);
			setAnswers(new Array(response.questions.length).fill(null));
			setLoading(false);
		} catch (err) {
			setError("Failed to load quiz");
			setLoading(false);
		}
	};

	const handleAnswerSelect = (questionIndex, answerIndex) => {
		const newAnswers = [...answers];
		newAnswers[questionIndex] = answerIndex;
		setAnswers(newAnswers);
	};

	const handleSubmit = async () => {
		if (answers.includes(null)) {
			setError("Please answer all questions before submitting.");
			return;
		}

		try {
			const response = await apiFetch({
				path: "/quiz-plugin/v1/quiz/submit",
				method: "POST",
				data: {
					quiz_id: quizId,
					answers: answers,
				},
			});
			setResults(response);
			setError(null);
		} catch (err) {
			setError("Failed to submit quiz");
		}
	};

	if (loading) {
		return <div>Loading quiz...</div>;
	}

	if (error) {
		return <div className="quiz-error">{error}</div>;
	}

	if (!quiz) {
		return <div>Quiz not found</div>;
	}

	if (results) {
		return (
			<div className="quiz-results">
				<h3>Quiz Results</h3>
				<div className="quiz-score">
					<p>
						Score: {results.correct_answers} out of {results.total_questions}
					</p>
					<p>Percentage: {Math.round(results.percentage)}%</p>
				</div>
				<div className="quiz-answers">
					{results.results.map((result, index) => (
						<div
							key={index}
							className={`question-result ${
								result.correct ? "correct" : "incorrect"
							}`}
						>
							<p>
								Question {index + 1}: {result.correct ? "✓" : "✗"}
							</p>
							<p>
								Correct answer:{" "}
								{quiz.questions[index].options[result.correct_answer]}
							</p>
						</div>
					))}
				</div>
				<button
					onClick={() => {
						setResults(null);
						setAnswers(new Array(quiz.questions.length).fill(null));
					}}
				>
					Try Again
				</button>
			</div>
		);
	}

	return (
		<div className="quiz-container">
			<h2>{quiz.title}</h2>
			<div className="quiz-questions">
				{quiz.questions.map((question, questionIndex) => (
					<div key={questionIndex} className="quiz-question">
						<h4>Question {questionIndex + 1}</h4>
						<p>{question.question}</p>
						<div className="quiz-options">
							{question.options.map((option, optionIndex) => (
								<label key={optionIndex} className="quiz-option">
									<input
										type="radio"
										name={`question-${questionIndex}`}
										checked={answers[questionIndex] === optionIndex}
										onChange={() =>
											handleAnswerSelect(questionIndex, optionIndex)
										}
									/>
									{option}
								</label>
							))}
						</div>
					</div>
				))}
			</div>
			<button onClick={handleSubmit} className="quiz-submit">
				Submit Quiz
			</button>
		</div>
	);
};

export default QuizRenderer;
