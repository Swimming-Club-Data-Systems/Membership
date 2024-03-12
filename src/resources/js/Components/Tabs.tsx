import React, {
    Children,
    ReactElement,
    ReactNode,
    useMemo,
    useState,
} from "react";
import { Tab } from "@headlessui/react";

function classNames(...classes: string[]) {
    return classes.filter(Boolean).join(" ");
}

type TabProps = {
    name: string;
    children: ReactNode;
};

const TabsChild = (props: TabProps) => {
    return <></>;
};

type TabsProps = {
    children: ReactNode | ReactNode[];
};

const Tabs = ({ children }: TabsProps) => {
    const [selectedIndex, setSelectedIndex] = useState(0);

    const filteredChildren = useMemo(
        () => Children.toArray(children).filter((child) => child),
        [children],
    ) as ReactElement[];

    return (
        <div>
            <Tab.Group
                selectedIndex={selectedIndex}
                onChange={setSelectedIndex}
            >
                <div className="sm:hidden">
                    <label htmlFor="tabs" className="sr-only">
                        Select a tab
                    </label>
                    {/* Use an "onChange" listener to redirect the user to the selected tab URL. */}
                    <select
                        id="tabs"
                        name="tabs"
                        className="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        defaultValue={
                            filteredChildren.find(
                                (tab, idx) => idx === selectedIndex,
                            ).props.name
                        }
                        onChange={(e) => {
                            const newIndex = filteredChildren.findIndex(
                                (tab, idx) => tab.props.name === e.target.value,
                            );
                            setSelectedIndex(newIndex);
                        }}
                    >
                        {filteredChildren.map((tab) => (
                            <option key={tab.props.name}>
                                {tab.props.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="hidden sm:block">
                    <Tab.List
                        as="nav"
                        className="isolate flex divide-x divide-gray-200 rounded-lg shadow"
                        aria-label="Tabs"
                    >
                        {filteredChildren.map(
                            (tab: ReactElement<TabProps>, tabIdx) => (
                                <Tab
                                    className={classNames(
                                        tabIdx === 0 ? "rounded-l-lg" : "",
                                        tabIdx === filteredChildren.length - 1
                                            ? "rounded-r-lg"
                                            : "",
                                        "group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 focus:z-10 ui-selected:text-gray-900 ui-not-selected:text-gray-500 ui-not-selected:hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none focus:ring-indigo-500",
                                    )}
                                >
                                    <span>{tab.props.name}</span>
                                    <span
                                        aria-hidden="true"
                                        className={classNames(
                                            // current
                                            //     ? "bg-indigo-500"
                                            //     : "bg-transparent",
                                            "absolute inset-x-0 bottom-0 h-0.5 ui-selected:bg-indigo-500 ui-not-selected:bg-transparent",
                                        )}
                                    />
                                </Tab>
                            ),
                        )}
                    </Tab.List>
                </div>
                <Tab.Panels>
                    {filteredChildren.map(
                        (tab: ReactElement<TabProps>, tabIdx) => (
                            <Tab.Panel as="div" className="mt-4">
                                {tab.props.children}
                            </Tab.Panel>
                        ),
                    )}
                </Tab.Panels>
            </Tab.Group>
        </div>
    );
};

export { Tabs, TabsChild as Tab };
