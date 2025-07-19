<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import NewsFeed from '@/components/NewsFeed.vue';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const isLoading = ref(true);
const summary = ref({
    total_articles: 0,
    total_sources: 0,
    new_articles: 0,
});

onMounted(() => {
    fetchSummary();
});

async function fetchSummary() {
    isLoading.value = true;
    const response = await fetch('/api/summary');
    if (!response.ok) {
        throw new Error('Failed to fetch summary');
    }
    const data = await response.json();
    summary.value = data;
    isLoading.value = false;
}

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern v-if="isLoading" />
                    <div v-else class="flex flex-col items-center justify-center h-full">
                        {{ summary.total_articles }}
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Articles</span>
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern v-if="isLoading" />
                    <div v-else class="flex flex-col items-center justify-center h-full">
                        {{ summary.total_sources }}
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Sources</span>
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern v-if="isLoading" />
                    <div v-else class="flex flex-col items-center justify-center h-full">
                        {{ summary.new_articles }}
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">New Articles</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border overflow-hidden">
                <NewsFeed />
            </div>
        </div>
    </AppLayout>
</template>
