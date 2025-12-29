<script setup lang="ts">
import { UFAdminSidebarMenuItems } from '@userfrosting/sprinkle-admin/components'
import { useLogoutApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
const config = useConfigStore()

// Auth and Logout API variables
const auth = useAuthStore()
const { submitLogout } = useLogoutApi()
</script>

<template>
    <!-- User card, visible only on mobile -->
    <UFSideBarUserCard
        class="uk-hidden@m"
        v-if="auth.isAuthenticated"
        :username="auth.user?.full_name"
        :avatar="auth.user?.avatar"
        :meta="auth.user?.user_name" />

    <!-- Navbar element repeated for mobile -->
    <UFSideBarItem class="uk-hidden@m" :to="{ name: 'about' }" :label="$t('ABOUT')" />
    <UFSideBarItem
        class="uk-hidden@m"
        :to="{ name: 'account.register' }"
        :label="$t('REGISTER')"
        v-if="!auth.isAuthenticated && config.get('site.registration.enabled')" />
    <UFSideBarItem
        class="uk-hidden@m"
        :to="{ name: 'account.login' }"
        :label="$t('LOGIN')"
        v-if="!auth.isAuthenticated" />
    <UFSideBarItem
        class="uk-hidden@m"
        :label="$t('ACCOUNT.SETTINGS')"
        v-if="$checkAccess('update_account_settings')"
        :to="{ name: 'account.settings' }" />
    <UFSideBarItem
        class="uk-hidden@m"
        :label="$t('LOGOUT')"
        @click="submitLogout()"
        v-if="auth.isAuthenticated" />

    <!-- Admin Panel Section, always visible -->
    <UFSideBarLabel :label="$t('ADMIN_PANEL')" v-if="$checkAccess('uri_dashboard')" />
    <UFAdminSidebarMenuItems />
</template>
