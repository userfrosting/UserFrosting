<script setup lang="ts">
import { useLogoutApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
const config = useConfigStore()

// Auth and Logout API variables
const auth = useAuthStore()
const { submitLogout } = useLogoutApi()
</script>

<template>
    <UFNavBar :title="config.get('site.title')" :to="{ name: 'home' }">
        <UFNavBarItem :to="{ name: 'about' }" :label="$t('ABOUT')" />
        <UFNavBarItem
            :to="{ name: 'account.register' }"
            :label="$t('REGISTER')"
            v-if="!auth.isAuthenticated && useConfigStore().get('site.registration.enabled')" />
        <UFNavBarLogin v-if="!auth.isAuthenticated" />
        <UFNavBarUserCard
            v-if="auth.isAuthenticated"
            :username="auth.user?.full_name"
            :avatar="auth.user?.avatar"
            :meta="auth.user?.user_name">
            <UFNavBarUserCardButton
                :label="$t('ADMIN_PANEL')"
                v-if="$checkAccess('uri_dashboard')"
                :to="{ name: 'admin.dashboard' }" />
            <UFNavBarUserCardButton
                :label="$t('ACCOUNT.SETTINGS')"
                v-if="$checkAccess('update_account_settings')"
                :to="{ name: 'account.settings' }" />
            <UFNavBarUserCardButton :label="$t('LOGOUT')" @click="submitLogout()" />
        </UFNavBarUserCard>
    </UFNavBar>
</template>
