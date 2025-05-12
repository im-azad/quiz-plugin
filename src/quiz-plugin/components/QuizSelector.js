import { SelectControl } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";

const QuizSelector = ({ value, onChange }) => {
	const quizzes = useSelect((select) => {
		return select(coreStore).getEntityRecords("postType", "quiz", {
			status: "publish",
			per_page: -1,
		});
	}, []);

	if (!quizzes) {
		return <p>Loading quizzes...</p>;
	}

	if (quizzes.length === 0) {
		return <p>No quizzes found. Please create a quiz first.</p>;
	}

	const options = [
		{ label: "Select a quiz...", value: "" },
		...quizzes.map((quiz) => ({
			label: quiz.title.rendered,
			value: quiz.id,
		})),
	];

	return (
		<SelectControl
			label="Select Quiz"
			value={value}
			options={options}
			onChange={onChange}
		/>
	);
};

export default QuizSelector;
