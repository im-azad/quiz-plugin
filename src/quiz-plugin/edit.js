import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import QuizSelector from './components/QuizSelector';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    
    return (
        <div { ...blockProps }>
            <QuizSelector
                value={attributes.quizId}
                onChange={(quizId) => setAttributes({ quizId: parseInt(quizId) })}
            />
            {!attributes.quizId && (
                <p className="quiz-placeholder">
                    {__('Select a quiz to display here', 'quiz-plugin')}
                </p>
            )}
        </div>
    );
}
