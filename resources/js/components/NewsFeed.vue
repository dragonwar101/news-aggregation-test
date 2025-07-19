<script setup lang="ts">
import { PaginatedResponse, Article } from '@/types';
import { ref, onMounted } from 'vue';
import Button from './ui/button/Button.vue';



const articles = ref<PaginatedResponse<Article>>({
  data: [],
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  },
  meta: {
    current_page: 1,
    from: 1,
    last_page: 1,
    path: '',
    per_page: 10,
    to: 10,
    total: 10,
  },
});
const isLoading = ref(true);
const isFetching = ref(false);
const error = ref<string | null>(null);

async function fetchNews(page: number = 1) {
  try {
    isFetching.value = true;
    const response = await fetch(`/api/news?page=${page}`);
    if (!response.ok) {
      throw new Error('Failed to fetch news');
    }
    const data = await response.json();
    articles.value = data;
  } catch (err: any) {
    error.value = err.message;
  } finally {
    isLoading.value = false;
    isFetching.value = false;
  }
}

onMounted(() => {
  fetchNews();
});
</script>

<template>
  <div class="news-feed-container bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Top Headlines</h2>
    <div v-if="isLoading" class="text-center text-gray-500 dark:text-gray-400">
      Loading news...
    </div>
    <div v-else-if="error" class="text-center text-red-500">
      {{ error }}
    </div>
    <ul v-else-if="articles.data.length" class="space-y-4">
      <li v-for="(article, index) in articles.data" :key="index" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100" v-html="article.title"></h3>
        <p class="text-gray-600 dark:text-gray-300 mt-1" v-html="article.description"></p>
        <a :href="article.url" target="_blank" class="text-blue-500 hover:underline mt-2 inline-block">
          Read more on {{ article.original_source }}
        </a>
      </li>
    </ul>
    <div v-else class="text-center text-gray-500 dark:text-gray-400">
      No news articles found.
    </div>
    <div v-if="articles.links.next" class="mt-4">
      <Button variant="default" @click="fetchNews(articles.meta.current_page + 1)" :disabled="isFetching">Load More</Button>
    </div>
  </div>
</template>
