import React from "react";
import type { Meta, StoryObj } from "@storybook/react";
import Alert from "@/Components/Alert";

const meta: Meta<typeof Alert> = {
    /* 👇 The title prop is optional.
     * See https://storybook.js.org/docs/react/configure/overview#configure-story-loading
     * to learn how to generate automatic titles
     */
    title: "Alert",
    component: Alert,
};

export default meta;

type Story = StoryObj<typeof Alert>;

/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/react/api/csf
 * to learn how to use render functions.
 */
export const Success: Story = {
    render: ({ children, ...args }) => <Alert {...args}>{children}</Alert>,
};
Success.args = {
    title: "Success",
    children: "Test",
    variant: "success",
};

export const Warning: Story = {
    render: ({ children, ...args }) => <Alert {...args}>{children}</Alert>,
};
Warning.args = {
    title: "Warning",
    children: "Test",
    variant: "warning",
};

export const Error: Story = {
    render: ({ children, ...args }) => <Alert {...args}>{children}</Alert>,
};
Error.args = {
    title: "Error",
    children: "Test",
    variant: "error",
};
