import React from "react";
import type { Meta, StoryObj } from "@storybook/react";
import { DefinitionList } from "@/Components/DefinitionList";

const meta: Meta<typeof DefinitionList> = {
    /* 👇 The title prop is optional.
     * See https://storybook.js.org/docs/react/configure/overview#configure-story-loading
     * to learn how to generate automatic titles
     */
    title: "DefinitionList",
    component: DefinitionList,
};

export default meta;

type Story = StoryObj<typeof DefinitionList>;

/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/react/api/csf
 * to learn how to use render functions.
 */
export const Default: Story = {
    render: ({ ...args }) => <DefinitionList {...args} />,
};
Default.args = {
    verticalPadding: 2,
    items: [
        {
            key: "header_name",
            term: "Entry maker name",
            definition: "Chris Heppell",
        },
        {
            key: "header_email",
            term: "Entry maker email",
            definition: "chrish@example.com",
            truncate: true,
        },
    ],
};
