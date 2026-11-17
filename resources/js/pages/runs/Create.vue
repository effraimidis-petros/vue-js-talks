<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import RunController from '@/actions/App/Http/Controllers/RunController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index } from '@/routes/runs';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Runs',
                href: index(),
            },
            {
                title: 'Log a run',
                href: create(),
            },
        ],
    },
});

defineProps<{
    today: string;
}>();
</script>

<template>
    <Head title="Log a run" />

    <div class="flex max-w-xl flex-col gap-6 p-4">
        <Heading
            title="Log a run"
            description="Date, distance and how long it took"
        />

        <Form
            v-bind="RunController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="ran_on">Date</Label>
                <Input
                    id="ran_on"
                    type="date"
                    name="ran_on"
                    :default-value="today"
                    required
                />
                <InputError :message="errors.ran_on" />
            </div>

            <div class="grid gap-2">
                <Label for="distance_km">Distance (km)</Label>
                <Input
                    id="distance_km"
                    type="number"
                    step="0.01"
                    name="distance_km"
                    required
                    placeholder="10.00"
                />
                <InputError :message="errors.distance_km" />
            </div>

            <div class="grid gap-2">
                <Label for="duration">Duration</Label>
                <Input
                    id="duration"
                    name="duration"
                    required
                    placeholder="mm:ss or h:mm:ss"
                />
                <InputError :message="errors.duration" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="log-run-button">
                    Log run
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
