// Transferred code from gutenblocks-main
export const Question = ({
	question,
	questionIndex,
	correctAnswer,
	onUpdateQuestion,
	onAddAnswer,
	onRemoveAnswer,
	onUpdateAnswer,
	onSetCorrectAnswer,
	onRemoveQuestion,
}) => (
	<div className="quiz-plugin-quiz__question">
		<div className="quiz-plugin-quiz__question-info">
			<TextControl
				label={__("Question", "quiz-plugin")}
				className="quiz-plugin-quiz__question-title"
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				value={question.question}
				onChange={(value) =>
					onUpdateQuestion(questionIndex, { question: value })
				}
			/>

			<div className="quiz-plugin-quiz__answers">
				{question.answers.map((answer, answerIndex) => (
					<BaseControl
						key={`${questionIndex}-answer-${answerIndex}`}
						className="quiz-plugin-quiz__answer"
						__nextHasNoMarginBottom
						label={__("Answer", "quiz-plugin")}
						id={`quiz-plugin-answer-${questionIndex}-${answerIndex}`}
					>
						<div className="quiz-plugin-quiz__answer-row">
							<TextControl
								__nextHasNoMarginBottom
								value={answer}
								onChange={(value) =>
									onUpdateAnswer(questionIndex, answerIndex, value)
								}
							/>
							{question.answers.length > 1 && (
								<Button
									className="quiz-plugin-quiz__remove-answer"
									isDestructive
									onClick={() => onRemoveAnswer(questionIndex, answerIndex)}
								>
									<Icon icon={trash} />
								</Button>
							)}
						</div>
					</BaseControl>
				))}

				<Button
					variant="secondary"
					className="quiz-plugin-quiz__add-answer"
					onClick={() => onAddAnswer(questionIndex)}
				>
					<Icon icon={plus} />
				</Button>
			</div>

			<RadioControl
				label={__("Select Correct Answer", "quiz-plugin")}
				selected={correctAnswer}
				options={question.answers.map((answer) => ({
					label: answer || __("(empty)", "quiz-plugin"),
					value: answer,
				}))}
				onChange={(value) => onSetCorrectAnswer(questionIndex, value)}
			/>
		</div>

		<Button
			variant="secondary"
			isDestructive
			icon={close}
			onClick={() => onRemoveQuestion(questionIndex)}
			className="quiz-plugin-quiz__remove-question"
		>
			{__("Remove Question", "quiz-plugin")}
		</Button>
	</div>
);
