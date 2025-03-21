<script setup lang="ts">
interface CmsPage {
    id: number;
    title: string;
    content: string;
    parent_id?: number | null;
    children?: CmsPage[];
}

const props = defineProps<{ subpage: CmsPage }>();

const parseTitle = (title: string) => {
    return title == undefined ? '' : `/${title.replace(/[^a-z0-9_]+/gi, '-').replace(/^-|-$/g, '').toLowerCase()}`
}
</script>

<template>
    <div class="subpage-tree">
        <div class="subpage-item">
            <span class="subpage-title">{{ parseTitle(props.subpage.title) }}</span>
            <button @click="$emit('manageSubpage', props.subpage)">
                Edit Subpage
            </button>
        </div>
        <div class="subpage-children" v-if="props.subpage.children && props.subpage.children.length">
            <SubpageTree
                v-for="child in props.subpage.children"
                :key="child.id"
                :subpage="child"
                @manageSubpage="$emit('manageSubpage', $event)"
            />
        </div>
    </div>
</template>

<style scoped>
.subpage-tree {
    margin-left: 20px;
    border-left: 1px dashed #ccc;
    padding-left: 10px;
    margin-top: 5px;
}
.subpage-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.subpage-title {
    font-weight: bold;
}
</style>
