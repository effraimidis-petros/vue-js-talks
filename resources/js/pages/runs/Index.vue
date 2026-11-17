<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { create, index } from '@/routes/runs';
import type { Run, WeeklySummary } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Runs',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    runs: Run[];
    summary: WeeklySummary;
}>();

const goalBarWidth = computed(() => `${props.summary.progress}%`);
</script>

<template>
    <Head title="Runs" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading title="Runs" description="Every run you have logged" />

            <Button as-child data-test="log-run-link">
                <Link :href="create()">
                    <Plus />
                    Log a run
                </Link>
            </Button>
        </div>

        <Card class="gap-4 py-5" data-test="weekly-summary">
            <CardHeader class="px-5">
                <CardTitle>This week</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col gap-3 px-5">
                <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
                    <span
                        class="text-3xl font-semibold tabular-nums"
                        data-test="weekly-summary-distance"
                    >
                        {{ summary.distance }}
                    </span>
                    <span
                        class="text-muted-foreground"
                        data-test="weekly-summary-runs"
                    >
                        {{ summary.runs }}
                    </span>
                </div>
                <div
                    class="h-2 overflow-hidden rounded-full bg-muted"
                    role="progressbar"
                    aria-label="Weekly goal"
                    :aria-valuenow="summary.progress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <div
                        class="h-full rounded-full bg-primary transition-[width]"
                        :style="{ width: goalBarWidth }"
                        data-test="weekly-summary-bar"
                    />
                </div>
                <p
                    class="text-sm text-muted-foreground tabular-nums"
                    data-test="weekly-summary-goal"
                >
                    {{ summary.goal }}
                </p>
            </CardContent>
        </Card>

        <div
            class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full text-left text-sm" data-test="runs-table">
                <thead class="border-b bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Distance
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Duration
                        </th>
                        <th class="px-4 py-3 text-right font-medium">Pace</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="run in runs"
                        :key="run.id"
                        class="border-b last:border-b-0"
                        data-test="run-row"
                    >
                        <td class="px-4 py-3">{{ run.ran_on_label }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            {{ run.distance }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            {{ run.duration }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            {{ run.pace }}
                        </td>
                    </tr>
                    <tr v-if="runs.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No runs yet. Log your first one!
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
