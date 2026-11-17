export type Run = {
    id: number;
    ran_on: string;
    ran_on_label: string;
    distance: string;
    duration: string;
    pace: string;
};

export type WeeklySummary = {
    distance: string;
    runs: string;
    goal: string;
    progress: number;
};
