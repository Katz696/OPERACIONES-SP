<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { NDialogProvider, NMessageProvider, NNotificationProvider } from 'naive-ui';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <n-dialog-provider>
        <n-notification-provider>
            <n-message-provider>
                <n-dialog-provider>
                    <AppLayout :breadcrumbs="breadcrumbs">
                        <slot />
                    </AppLayout>
                </n-dialog-provider>
            </n-message-provider>
        </n-notification-provider>
    </n-dialog-provider>
</template>
