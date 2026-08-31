<script>
export default {
    props: {
        title: { type: String, required: true },
        help: { type: String, default: '' },
        name: { type: String, required: true },
        enabled: { type: Boolean, default: true },
        initiallyOpen: { type: Boolean, default: false },
    },
    data() {
        return {
            isOpen: this.initiallyOpen,
            isEnabled: Boolean(this.enabled),
        };
    },
    computed: {
        panelId() {
            return `home-editor-${this.name.replace(/[^a-z0-9]+/gi, '-')}`;
        },
    },
};
</script>

<template>
    <section class="card home-section-editor mb-3">
        <div class="card-header home-section-editor__header">
            <button
                class="home-section-editor__toggle"
                type="button"
                :aria-expanded="isOpen ? 'true' : 'false'"
                :aria-controls="panelId"
                @click="isOpen = !isOpen"
            >
                <span class="home-section-editor__chevron" :class="{ 'is-open': isOpen }" aria-hidden="true">›</span>
                <span>
                    <strong>{{ title }}</strong>
                    <small v-if="help" class="d-block text-muted mt-1">{{ help }}</small>
                </span>
            </button>

            <div class="custom-control custom-switch ml-3" @click.stop>
                <input type="hidden" :name="`${name}[enabled]`" value="0">
                <input
                    :id="`${panelId}-enabled`"
                    v-model="isEnabled"
                    class="custom-control-input"
                    type="checkbox"
                    :name="`${name}[enabled]`"
                    value="1"
                >
                <label class="custom-control-label text-nowrap" :for="`${panelId}-enabled`">
                    {{ $t('admin.display') }}
                </label>
            </div>
        </div>

        <div v-show="isOpen" :id="panelId" class="card-body home-section-editor__body">
            <slot />
        </div>
    </section>
</template>

<style scoped>
.home-section-editor {
    overflow: hidden;
    border: 1px solid #e2e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(23, 29, 35, .035);
}

.home-section-editor__header {
    display: flex;
    min-height: 68px;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: #f8f9fa;
}

.home-section-editor__toggle {
    display: flex;
    min-width: 0;
    flex: 1;
    align-items: center;
    gap: 12px;
    padding: 0;
    border: 0;
    background: transparent;
    color: inherit;
    text-align: left;
}

.home-section-editor__chevron {
    color: #8b929a;
    font-size: 26px;
    line-height: 1;
    transition: transform .18s ease;
}

.home-section-editor__chevron.is-open {
    transform: rotate(90deg);
}

.home-section-editor__body {
    padding: 22px 20px;
}

@media (max-width: 575px) {
    .home-section-editor__header {
        align-items: flex-start;
    }
}
</style>
