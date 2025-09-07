/** Create App */
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)

/** Setup Pinia */
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

/** Setup Router */
import router from './router'
app.use(router)

/** Setup Core Sprinkle */
import CoreSprinkle from '@userfrosting/sprinkle-core'
app.use(CoreSprinkle)

/** Setup Account Sprinkle */
import AccountSprinkle from '@userfrosting/sprinkle-account'
app.use(AccountSprinkle, { router })

/** Setup Admin Sprinkle */
import AdminSprinkle from '@userfrosting/sprinkle-admin'
app.use(AdminSprinkle)

/** Setup Theme */
import '@userfrosting/theme-pink-cupcake/less/main.less'
import PinkCupcake from '@userfrosting/theme-pink-cupcake'
app.use(PinkCupcake)

// Done
app.mount('#app')
