<?php
/**
 * Quiz Block Renderer
 *
 * Renders a quiz block with questions and multiple-choice answers.
 *
 * @package Quiz_Plugin
 *
 * @param array     $attributes Block attributes.
 */

// Validate quiz ID
$quiz_id = $attributes['id'] ?? 0;

if ( ! $quiz_id ) {
	return;
}

// Fetch quiz data
$quiz      = get_post( $quiz_id );
$quiz_data = json_decode( get_post_meta( $quiz_id, 'quiz_questions', true ), true );

if ( empty( $quiz_data ) ) {
	return;
}

// Interactivity state for the quiz
wp_interactivity_state(
	'quiz-plugin/quiz',
	array(
		'answered'          => 0,
		'correct'           => 0,
		'allCorrect'        => false,
		'site_url'          => get_site_url(),
		'quiz_id'           => $quiz_id,
		'modal_title'       => __( 'Quiz Results', 'quiz-plugin' ),
		'all_correct_title' => __( '🥳 All correct, congratulations!', 'quiz-plugin' ),
	)
);

$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => 'quiz-plugin-quiz' ] );
$total_questions = count( $quiz_data );
?>

<div 
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	data-wp-interactive="quiz-plugin/quiz"
>
	<h2 class="quiz-plugin-quiz__title">
		<?php echo esc_html( $quiz->post_title ); ?>
	</h2>

	<?php if ( ! empty( $quiz->post_content ) ) : ?>
		<div class="quiz-plugin-quiz__description">
			<?php echo wp_kses_post( $quiz->post_content ); ?>
		</div>
	<?php endif; ?>

	<div class="quiz-plugin-quiz__questions">
		<?php foreach ( $quiz_data as $index => $question ) : ?>
			<div class="quiz-plugin-quiz__question" data-question-index="<?php echo esc_attr( $index ); ?>">
				<h3 class="quiz-plugin-quiz__question-title">
					<?php echo esc_html( $question['question'] ); ?>
				</h3>

				<div class="quiz-plugin-quiz__answers">
					<?php foreach ( $question['answers'] as $answer ) : ?>
						<button 
							class="quiz-plugin-quiz__answer"
							data-answer="<?php echo esc_attr( $answer ); ?>"
							data-wp-on--click="actions.checkAnswer"
						>
							<?php echo esc_html( $answer ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div 
		class="quiz-plugin-quiz__results"
		data-wp-bind--hidden="!state.allCorrect"
	>
		<h3><?php echo esc_html( __( 'Quiz Results', 'quiz-plugin' ) ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: 1: Number of correct answers, 2: Total number of questions */
				esc_html__( 'You got %1$s out of %2$s questions correct!', 'quiz-plugin' ),
				'<span data-wp-text="state.correct"></span>',
				esc_html( $total_questions )
			);
			?>
		</p>
	</div>
</div>