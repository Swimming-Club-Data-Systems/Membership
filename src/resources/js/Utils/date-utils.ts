import { format, fromUnixTime, parseISO } from "date-fns";

export const formatDateTime = (isoDate: string): string => {
    return format(parseISO(isoDate), "HH:mm, do MMMM yyyy");
};

export const formatDate = (isoDate: string): string => {
    return format(parseISO(isoDate), "do MMMM yyyy");
};

export const formatTime = (isoDate: string): string => {
    return format(parseISO(isoDate), "HH:mm");
};

export const formatUnixTime = (unixTime: number): string => {
    return format(fromUnixTime(unixTime), "HH:mm, do MMMM yyyy");
};
