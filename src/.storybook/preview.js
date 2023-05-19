// .storybook/preview.js

// import '../src/tailwind.css'; // replace with the name of your tailwind css file
import "../resources/css/app.css";

/** @type { import('@storybook/react').Preview } */
const preview = {
    parameters: {
        actions: { argTypesRegex: "^on[A-Z].*" },
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/,
            },
        },
    },
};

export default preview;
