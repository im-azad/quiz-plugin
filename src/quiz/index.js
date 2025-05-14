import { useState } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { Button, TextControl } from "@wordpress/components";
import "./style.scss";

const QuizBlock = () => {
	const [question, setQuestion] = useState("");
	const [answers, setAnswers] = useState([]);
	const [correctAnswer, setCorrectAnswer] = useState(null);

	const addAnswer = () => {
		setAnswers([...answers, ""]);
	};

	const removeAnswer = (index) => {
		setAnswers(answers.filter((_, i) => i !== index));
	};

	const updateAnswer = (index, value) => {
		const newAnswers = [...answers];
		newAnswers[index] = value;
		setAnswers(newAnswers);
	};

	const saveQuiz = () => {
		// Logic to save quiz using WordPress data
	};

	return (
		<div className="quiz-block">
			<TextControl
				label="Question"
				value={question}
				onChange={(value) => setQuestion(value)}
			/>
			{answers.map((answer, index) => (
				<div key={index} className="answer">
					<TextControl
						value={answer}
						onChange={(value) => updateAnswer(index, value)}
					/>
					<Button isDestructive onClick={() => removeAnswer(index)}>
						Delete
					</Button>
				</div>
			))}
			<Button isPrimary onClick={addAnswer}>
				Add Answer
			</Button>
			<Button isPrimary onClick={saveQuiz}>
				Save Quiz
			</Button>
		</div>
	);
};

export default QuizBlock;
