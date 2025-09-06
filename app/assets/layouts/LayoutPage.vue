<script setup lang="ts">
import NavBar from '../components/NavBar.vue'
import FooterContent from '../components/FooterContent.vue'
</script>

<template>
    <NavBar />
    <UFMainContent>
        <UFHeaderPage />
        <RouterView />
    </UFMainContent>
    <FooterContent />
</template>
