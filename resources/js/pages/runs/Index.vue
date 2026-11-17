<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/runs';
import type { Run } from '@/types';

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

defineProps<{
    runs: Run[];
}>();
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
