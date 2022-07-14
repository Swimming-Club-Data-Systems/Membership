import React, { useEffect, useRef } from "react";
import * as monaco from "monaco-editor";

const CodeEditor = (props) => {
  const ref = useRef();

  useEffect(() => {
    monaco.editor.create(ref.current, {
      value: props.value,
      language: props.language,
      scrollBeyondLastLine: false,
      wordWrap: "on",
      minimap: {
        enabled: false,
      },
      automaticLayout: true,
    });
  }, []);

  return (
    <div className="rounded-md border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <div className="h-48 w-full" ref={ref}></div>
    </div>
  );
};

export default CodeEditor;
