/**
 * WordPress dependencies
 */
import apiFetch from "@wordpress/api-fetch";
import { useCallback, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

/**
 * Constants for the quiz data.
 */
export const DEFAULT_QUIZ_STATE = {
	title: "",
	content: "",
	questions: [],
	correct_answers: [],
};

/**
 * Constants for the quiz question.
 */
export const DEFAULT_QUESTION = {
	question: "",
	answers: [""],
};

/**
 * Hook to fetch and save the quiz data.
 *
 * @param {number}   id            The ID of the quiz.
 * @param {Function} onSaveSuccess The function to call when the quiz is saved.
 */
export const useQuizData = (id, onSaveSuccess) => {
	const [quizData, setQuizData] = useState(DEFAULT_QUIZ_STATE);
	const [error, setError] = useState(null);
	const [isSaving, setIsSaving] = useState(false);

	const fetchQuizData = useCallback(async (quizId) => {
		try {
			setError(null);
			const response = await apiFetch({
				path: `/quiz-plugin/v1/quizzes/${quizId}`,
			});

			if (!response) {
				throw new Error("Quiz not found");
			}

			setQuizData({
				title: response.title || "",
				content: response.content || "",
				questions: response.questions || [],
				correct_answers: response.correct_answers || [],
			});
		} catch (err) {
			setError(
				__("Failed to load quiz data. Please try again.", "quiz-plugin"),
			);
			setQuizData(DEFAULT_QUIZ_STATE);
		}
	}, []);

	/**
	 * Hook to save the quiz data.
	 *
	 * @return {void}
	 */
	const saveQuiz = async () => {
		// If the quiz is already saving, don't save it again.
		if (isSaving) {
			return;
		}

		try {
			setIsSaving(true);
			setError(null);

			const response = await apiFetch({
				path: id ? `/quiz-plugin/v1/quizzes/${id}` : "/quiz-plugin/v1/quizzes",
				method: id ? "PUT" : "POST",
				data: quizData,
			});

			if (!id) {
				onSaveSuccess(response.id);
			}
		} catch (err) {
			setError(__("Failed to save quiz. Please try again.", "quiz-plugin"));
		} finally {
			setIsSaving(false);
		}
	};

	return {
		quizData,
		setQuizData,
		error,
		isSaving,
		fetchQuizData,
		saveQuiz,
	};
};
