<template>
  <div class="flex items-center justify-between mt-4 mb-8">
    <div class="flex flex-1 justify-center sm:hidden gap-3">
      <a v-if="previousURL" :href="previousURL" class="btn">Previous</a>
      <a v-if="nextURL" :href="nextURL" class="btn">Next</a>
    </div>

    <div class="hidden sm:block mx-auto">
      <nav class="isolate inline-flex -space-x-px gap-1" aria-label="Pagination">
        <a v-if="previousURL" :href="previousURL" class="relative inline-flex items-center bg-brand-cobalt hover:bg-brand-cobalt px-2 py-2 text-sm font-medium text-white hover:bg-brand-cobalt min-w-10">
          <span class="sr-only">Previous</span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
        </a>

        <span v-if="hellipLeft" class="relative inline-flex items-center bg-white px-4 py-2 text-sm font-medium text-gray-700 border border-design-offGreige hover:border-brand-cobalt min-w-10">&hellip;</span>

        <template v-for="n in totalPages">
          <component
            v-if="n-1 ==  current || (n-1 < current && n-1 > current - (maxLinks/2)-1) || (n-1 > current && n-1 < current + (maxLinks/2)+1)"
            :is="n-1 == current ? 'span' : 'a'"
            :href="'?page=' + (n-1)"
            :class="{
              'relative z-10 inline-flex items-center bg-brand-cobalt px-4 py-2 text-sm font-medium text-white min-w-10': n-1 == current,
              'relative inline-flex items-center bg-white px-4 py-2 text-sm font-medium hover:bg-brand-cobalt hover:text-white border border-design-offGreige hover:border-brand-cobalt min-w-10': n - 1 != current
            }">
            {{ n }}
          </component>
        </template>

        <span v-if="hellipRight" class="relative inline-flex items-center bg-white px-4 py-2 text-sm font-medium text-gray-700 border border-design-offGreige hover:border-brand-cobalt min-w-10">&hellip;</span>

        <a v-if="nextURL" :href="nextURL" class="relative inline-flex items-center bg-brand-cobalt hover:bg-brand-cobalt px-2 py-2 text-sm font-medium text-white hover:bg-brand-cobalt min-w-10">
          <span class="sr-only">Next</span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </a>
      </nav>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
  props: {
    current: {default: 0},
    totalPages: {default: 0},
    maxLinks: {default: 8}
  },
  data() {
    return {
      previousURL: this.current > 0 ? '?page=' + (this.current - 1) : null,
      nextURL: this.current + 1 < this.totalPages ? '?page=' + (this.current + 1) : null,
      hellipLeft: this.current > (this.maxLinks / 2) + 1,
      hellipRight: this.totalPages - this.current > (this.maxLinks / 2)
    }
  }
}
</script>
