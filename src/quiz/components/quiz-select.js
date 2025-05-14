/**
 * WordPress dependencies
 */
import { Spinner } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export const QuizSelect = ({ quizzes, isLoading, onSelect }) => {
	if (isLoading) {
		return <Spinner />;
	}

	if (!quizzes.length) {
		return <p>{__("No quizzes found. Create a new one!", "quiz-plugin")}</p>;
	}

	return (
		<div className="quiz-plugin-quiz__select-list">
			<h3>{__("Select an existing quiz:", "quiz-plugin")}</h3>
			<ul>
				{quizzes.map((quiz) => (
					<li key={quiz.id}>
						<button type="button" onClick={() => onSelect(quiz.id)}>
							{quiz.title.rendered}
						</button>
					</li>
				))}
			</ul>
		</div>
	);
};

// Transferred code from gutenblocks-main
