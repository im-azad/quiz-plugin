/**
 * WordPress dependencies.
 */
import { useBlockProps } from "@wordpress/block-editor";
import { Button, Icon, TextControl } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { addCard, addSubmenu, help } from "@wordpress/icons";

// Components.
import { Question } from "./components/question";
import { QuizSelect } from "./components/quiz-select";
import "./editor.scss";

// Hooks.
import { DEFAULT_QUESTION, DEFAULT_QUIZ_STATE, useQuizData } from "./hooks";

export default function Edit({ attributes, setAttributes }) {
	const { id } = attributes;
	const blockProps = useBlockProps();

	const { quizzes, isLoading } = useSelect((select) => ({
		quizzes:
			select("core").getEntityRecords("postType", "quiz", {
				per_page: -1,
			}) || [],
		isLoading: select("core").isResolving("getEntityRecords", [
			"postType",
			"quiz",
			{ per_page: -1 },
		]),
	}));

	const { quizData, setQuizData, error, isSaving, fetchQuizData, saveQuiz } =
		useQuizData(id, (newId) => setAttributes({ id: newId }));
	const [showForm, setShowForm] = useState(false);

	useEffect(() => {
		if (id && id !== 0) {
			fetchQuizData(id);
		} else {
			setQuizData(DEFAULT_QUIZ_STATE);
		}
	}, [id, fetchQuizData, setQuizData]);

	const updateQuestion = (index, updates) => {
		setQuizData((prev) => ({
			...prev,
			questions: prev.questions.map((q, idx) =>
				idx === index ? { ...q, ...updates } : q,
			),
		}));
	};

	const addQuestion = () => {
		setQuizData((prev) => ({
			...prev,
			questions: [...prev.questions, { ...DEFAULT_QUESTION }],
			correct_answers: [...prev.correct_answers, ""],
		}));
	};

	const removeQuestion = (index) => {
		setQuizData((prev) => ({
			...prev,
			questions: prev.questions.filter((_, idx) => idx !== index),
			correct_answers: prev.correct_answers.filter((_, idx) => idx !== index),
		}));
	};

	const addAnswer = (questionIndex) => {
		setQuizData((prev) => ({
			...prev,
			questions: prev.questions.map((q, idx) =>
				idx === questionIndex ? { ...q, answers: [...q.answers, ""] } : q,
			),
		}));
	};

	const removeAnswer = (questionIndex, answerIndex) => {
		setQuizData((prev) => ({
			...prev,
			questions: prev.questions.map((q, idx) =>
				idx === questionIndex
					? {
							...q,
							answers: q.answers.filter((_, aIdx) => aIdx !== answerIndex),
					  }
					: q,
			),
		}));
	};

	const updateAnswer = (questionIndex, answerIndex, value) => {
		setQuizData((prev) => ({
			...prev,
			questions: prev.questions.map((q, idx) =>
				idx === questionIndex
					? {
							...q,
							answers: q.answers.map((a, aIdx) =>
								aIdx === answerIndex ? value : a,
							),
					  }
					: q,
			),
		}));
	};

	const setCorrectAnswer = (questionIndex, value) => {
		setQuizData((prev) => ({
			...prev,
			correct_answers: prev.correct_answers.map((a, idx) =>
				idx === questionIndex ? value : a,
			),
		}));
	};

	return (
		<div {...blockProps}>
			{!id && !showForm && (
				<div className="quiz-plugin-quiz__select">
					<QuizSelect
						quizzes={quizzes}
						isLoading={isLoading}
						onSelect={(selectedId) => setAttributes({ id: selectedId })}
					/>
					<Button
						variant="primary"
						icon={addCard}
						onClick={() => setShowForm(true)}
					>
						{__("Create New Quiz", "quiz-plugin")}
					</Button>
				</div>
			)}

			{(id || showForm) && (
				<div className="quiz-plugin-quiz__edit">
					<TextControl
						label={__("Quiz Title", "quiz-plugin")}
						value={quizData.title}
						onChange={(value) =>
							setQuizData((prev) => ({
								...prev,
								title: value,
							}))
						}
					/>

					<TextControl
						label={__("Quiz Description", "quiz-plugin")}
						value={quizData.content}
						onChange={(value) =>
							setQuizData((prev) => ({
								...prev,
								content: value,
							}))
						}
					/>

					<div className="quiz-plugin-quiz__questions">
						{quizData.questions.map((question, index) => (
							<Question
								key={index}
								question={question}
								questionIndex={index}
								correctAnswer={quizData.correct_answers[index]}
								onUpdateQuestion={updateQuestion}
								onAddAnswer={addAnswer}
								onRemoveAnswer={removeAnswer}
								onUpdateAnswer={updateAnswer}
								onSetCorrectAnswer={setCorrectAnswer}
								onRemoveQuestion={removeQuestion}
							/>
						))}

						<Button variant="secondary" icon={addSubmenu} onClick={addQuestion}>
							{__("Add Question", "quiz-plugin")}
						</Button>
					</div>

					{error && <div className="quiz-plugin-quiz__error">{error}</div>}

					<Button variant="primary" isBusy={isSaving} onClick={saveQuiz}>
						{__("Save Quiz", "quiz-plugin")}
					</Button>
				</div>
			)}
		</div>
	);
}
