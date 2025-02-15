<script setup lang="ts">
import { computed } from 'vue'
import { DateTime } from 'luxon'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useConfigStore, useTranslator } from '@userfrosting/sprinkle-core/stores'

const auth = useAuthStore()
const config = useConfigStore()
const { translate } = useTranslator()

const helloMsg = computed(() => {
    return translate('WELCOME_TO', {
        title: config.get('site.title'),
        user: auth.user?.full_name ?? '&GUEST'
    })
})
</script>

<template>
    <article class="uk-article">
        <h1 class="uk-article-title">{{ helloMsg }}</h1>
        <p class="uk-article-meta">{{ $tdate(DateTime.now().toISO()) }}</p>
        <p class="uk-text-lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
            incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
            exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
            dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
            mollit anim id est laborum.
        </p>

        <div class="uk-grid-small uk-child-width-auto" uk-grid>
            <div>
                <a class="uk-button uk-button-text" href="#">{{ $t('READ_MORE') }}</a>
            </div>
        </div>
    </article>

    <hr class="uk-divider-icon" style="padding-top: 25px; padding-bottom: 25px" />

    <div class="uk-child-width-1-2@s uk-grid-match" uk-grid>
        <div>
            <div class="uk-card uk-card-hover uk-card-body">
                <h3 class="uk-card-title">Lorem ipsum</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua.
                </p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-hover uk-card-body">
                <h3 class="uk-card-title">Lorem ipsum</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua.
                </p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-hover uk-card-body">
                <h3 class="uk-card-title">Lorem ipsum</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua.
                </p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-hover uk-card-body">
                <h3 class="uk-card-title">Lorem ipsum</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua.
                </p>
            </div>
        </div>
    </div>

    <div style="padding-top: 25px"></div>

    <table class="uk-table uk-table-divider">
        <thead>
            <tr>
                <th>Table Heading</th>
                <th>Table Heading</th>
                <th>Table Heading</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Table Data</td>
                <td>Table Data</td>
                <td>Table Data</td>
            </tr>
            <tr>
                <td>Table Data</td>
                <td>Table Data</td>
                <td>Table Data</td>
            </tr>
            <tr>
                <td>Table Data</td>
                <td>Table Data</td>
                <td>Table Data</td>
            </tr>
        </tbody>
    </table>
</template>
