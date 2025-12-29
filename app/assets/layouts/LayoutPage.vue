<script setup lang="ts">
import NavBarContent from '../components/NavBarContent.vue'
import SideBarContent from '../components/SideBarContent.vue'
import FooterContent from '../components/FooterContent.vue'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
const config = useConfigStore()
</script>

<template>
    <UFNavBar :title="config.get('site.title')" :to="{ name: 'home' }">
        <NavBarContent />
    </UFNavBar>

    <!-- Sidebar for small screens -->
    <UFSideBar class="uk-hidden@m">
        <SideBarContent />
    </UFSideBar>

    <UFMainContent>
        <UFHeaderPage />
        <RouterView />
    </UFMainContent>

    <UFFooter>
        <FooterContent />
    </UFFooter>
</template>
