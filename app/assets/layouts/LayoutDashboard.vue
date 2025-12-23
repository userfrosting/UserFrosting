<script setup lang="ts">
import NavBar from '../components/NavBar.vue'
import FooterContent from '../components/FooterContent.vue'
import { UFAdminSidebarMenuItems } from '@userfrosting/sprinkle-admin/components'
</script>

<template>
    <NavBar />
    <UFSideBar>
        <UFSideBarLabel :label="$t('ADMIN_PANEL')" />
        <UFAdminSidebarMenuItems />
    </UFSideBar>
    <UFMainContent>
        <UFHeaderPage />
        <RouterView />
    </UFMainContent>
    <FooterContent />
</template>
