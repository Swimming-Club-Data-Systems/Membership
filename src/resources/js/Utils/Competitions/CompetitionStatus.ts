/**
 * Convert the status enum to a user readable string
 * @param status
 */
export const competitionStatus = (status: string): string => {
    switch (status) {
        case "draft":
            return "Draft";
        case "published":
            return "Published";
        case "paused":
            return "Paused";
        case "closed":
            return "Closed";
        case "cancelled":
            return "Cancelled";
    }
};
