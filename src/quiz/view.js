/**
 * WordPress dependencies
 */
import { store } from "@wordpress/interactivity";

/**
 * Quiz store configuration.
 */
store("quiz-plugin/quiz", {
	state: {
		get allCorrect() {
			return this.answered === this.correct;
		},
	},
	actions: {
		checkAnswer: async (event) => {
			const { state } = store("quiz-plugin/quiz").getContext();
			const button = event.target;
			const answer = button.dataset.answer;
			const questionIndex = button.closest("[data-question-index]").dataset
				.questionIndex;

			try {
				const response = await fetch(
					`${state.site_url}/wp-json/quiz-plugin/v1/quizzes/${state.quiz_id}/check`,
					{
						method: "POST",
						headers: {
							"Content-Type": "application/json",
						},
						body: JSON.stringify({
							question_index: questionIndex,
							answer,
						}),
					},
				);

				if (!response.ok) {
					throw new Error("Network response was not ok");
				}

				const data = await response.json();

				if (data.correct) {
					state.correct++;
					button.classList.add("quiz-plugin-quiz__answer--correct");
				} else {
					button.classList.add("quiz-plugin-quiz__answer--incorrect");
				}

				state.answered++;

				// Disable all answers for this question
				const answers = button
					.closest(".quiz-plugin-quiz__answers")
					.querySelectorAll(".quiz-plugin-quiz__answer");
				answers.forEach((answerButton) => {
					answerButton.disabled = true;
				});
			} catch (error) {
				console.error("Error:", error);
			}
		},
	},
});
