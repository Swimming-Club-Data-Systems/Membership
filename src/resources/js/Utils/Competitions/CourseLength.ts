/**
 * Convert the course length enum to a user readable string
 * @param course
 */
export const courseLength = (course: string): string => {
    switch (course) {
        case "short":
            return "Short course";
        case "long":
            return "Long course";
        case "open_water":
            return "Open water";
        case "not_applicable":
        case "irregular":
            return "N/A";
    }
};
