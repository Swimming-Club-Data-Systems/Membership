import React from "react";
import { PaperClipIcon } from "@heroicons/react/24/outline";

export type FileProps = {
    name: string;
    url: string;
    mime_type: string;
};

export type FileListProps = {
    items: FileProps[];
};

export const FileList: React.FC<FileListProps> = ({ items }) => {
    if (items.length === 0) {
        return;
    }

    return (
        <ul className="divide-y divide-gray-200 rounded-md border border-gray-200">
            {items.map((item) => {
                let isDownload = true;
                switch (item.mime_type) {
                    case "application/pdf":
                        isDownload = false;
                        break;
                }

                // Wildcard test for videos and images
                if (
                    item.mime_type.includes("image") ||
                    item.mime_type.includes("video")
                ) {
                    isDownload = false;
                }

                return (
                    <li
                        key={item.url}
                        className="flex items-center justify-between py-3 pl-3 pr-4 text-sm"
                    >
                        <div className="flex w-0 flex-1 items-center">
                            <PaperClipIcon
                                className="h-5 w-5 flex-shrink-0 text-gray-400"
                                aria-hidden="true"
                            />
                            <span className="ml-2 w-0 flex-1 truncate">
                                {item.name}
                            </span>
                        </div>
                        <div className="ml-4 flex-shrink-0">
                            <a
                                target="_blank"
                                href={item.url}
                                className="font-medium text-indigo-600 hover:text-indigo-500"
                                rel="noreferrer"
                                download={isDownload}
                            >
                                Download
                            </a>
                        </div>
                    </li>
                );
            })}
        </ul>
    );
};

export default FileList;
