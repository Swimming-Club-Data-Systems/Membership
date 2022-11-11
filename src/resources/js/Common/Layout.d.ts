import { ReactNode } from "react";

export interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}
