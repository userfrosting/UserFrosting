<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
const router = useRouter()
const config = useConfigStore()

// Logout API variables
const auth = useAuthStore()
</script>

<template>
    <UFNavBar :title="config.get('site.title')" :to="{ name: 'home' }">
        <UFNavBarItem :to="{ name: 'about' }" :label="$t('ABOUT')" />
        <UFNavBarItem
            :to="{ name: 'account.register' }"
            :label="$t('REGISTER')"
            v-if="!auth.isAuthenticated" />
        <UFNavBarLogin
            v-if="!auth.isAuthenticated"
            @goto-registration="router.push({ name: 'account.register' })" />
        <UFNavBarUserCard
            v-if="auth.isAuthenticated"
            :username="auth.user.full_name"
            :avatar="auth.user.avatar"
            :meta="auth.user.user_name">
            <UFNavBarUserCardButton :label="$t('ADMIN_PANEL')" :to="{ name: 'admin.dashboard' }" />
            <UFNavBarUserCardButton
                :label="$t('ACCOUNT.SETTINGS')"
                :to="{ name: 'account.settings' }" />
            <UFNavBarUserCardButton :label="$t('LOGOUT')" @click="auth.logout()" />
        </UFNavBarUserCard>
    </UFNavBar>
</template>
