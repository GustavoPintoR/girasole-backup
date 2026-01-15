<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

// Props and v-model
const props = defineProps<{
  modelValue: string | null
  placeholder?: string
  disabled?: boolean
  minHeightClass?: string
  maxHeightClass?: string
}>();
const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'focus'): void
  (e: 'blur'): void
}>();

// Editor setup
const editor = useEditor({
  extensions: [
    StarterKit.configure({
      heading: { levels: [1, 2, 3] },
      bulletList: { keepMarks: true, keepAttributes: false },
      orderedList: { keepMarks: true, keepAttributes: false },
      blockquote: true,
      codeBlock: true,
      horizontalRule: true,
      dropcursor: { width: 2, class: 'dropcursor' },
      gapcursor: true,
      history: true,
    }),
    Link.configure({
      autolink: true,
      openOnClick: true,
      linkOnPaste: true,
      protocols: ['http', 'https', 'mailto', 'tel'],
    }),

  ],
  content: props.modelValue || '',
  editable: !props.disabled,
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
  onFocus: () => emit('focus'),
  onBlur: () => emit('blur'),
});

// Keep external model in sync (e.g., form reset)
watch(
  () => props.modelValue,
  (val) => {
    if (!editor?.value) return;
    const current = editor.value.getHTML();
    if ((val ?? '') !== current) {
      editor.value.commands.setContent(val || '', false);
    }
  }
);

// React to disabled prop
watch(
  () => props.disabled,
  (d) => editor?.value?.setEditable(!d)
);

onBeforeUnmount(() => {
  editor?.value?.destroy();
});

// Toolbar commands
const canUndo = computed(() => editor?.value?.can().undo() ?? false);
const canRedo = computed(() => editor?.value?.can().redo() ?? false);
const isActive = (name: string, attrs: Record<string, any> = {}) =>
  editor?.value?.isActive(name as any, attrs) ?? false;

const toggle = {
  bold: () => editor?.value?.chain().focus().toggleBold().run(),
  italic: () => editor?.value?.chain().focus().toggleItalic().run(),
  strike: () => editor?.value?.chain().focus().toggleStrike().run(),
  heading: (level: number) => editor?.value?.chain().focus().toggleHeading({ level }).run(),
  bulletList: () => editor?.value?.chain().focus().toggleBulletList().run(),
  orderedList: () => editor?.value?.chain().focus().toggleOrderedList().run(),
  blockquote: () => editor?.value?.chain().focus().toggleBlockquote().run(),
  codeBlock: () => editor?.value?.chain().focus().toggleCodeBlock().run(),
  hr: () => editor?.value?.chain().focus().setHorizontalRule().run(),
  undo: () => editor?.value?.chain().focus().undo().run(),
  redo: () => editor?.value?.chain().focus().redo().run(),
  link: () => {
    const url = window.prompt('Enter URL');
    if (url === null) return;
    if (url === '') {
      editor?.value?.chain().focus().unsetLink().run();
      return;
    }
    editor?.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  },
  clear: () => editor?.value?.chain().focus().clearNodes().unsetAllMarks().run(),
};
</script>

<template>
  <div class="space-y-2">
    <!-- Toolbar: map to shadcn-vue Button / Toggle primitive styles -->
    <div class="flex flex-wrap gap-1 border rounded-md p-1 bg-background">
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('bold') }" @click="toggle.bold">B</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('italic') }" @click="toggle.italic"><i>I</i></button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('strike') }" @click="toggle.strike">S</button>

      <div class="w-px h-5 bg-border mx-1" />

      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('heading', { level: 1 }) }" @click="toggle.heading(1)">H1</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('heading', { level: 2 }) }" @click="toggle.heading(2)">H2</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('heading', { level: 3 }) }" @click="toggle.heading(3)">H3</button>

      <div class="w-px h-5 bg-border mx-1" />

      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('bulletList') }" @click="toggle.bulletList">• List</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('orderedList') }" @click="toggle.orderedList">1. List</button>

      <div class="w-px h-5 bg-border mx-1" />

      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('blockquote') }" @click="toggle.blockquote">“Quote”</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent"
              :class="{ 'bg-accent': isActive('codeBlock') }" @click="toggle.codeBlock">{ }</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent" @click="toggle.hr">HR</button>

      <div class="w-px h-5 bg-border mx-1" />

      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent" @click="toggle.link">Link</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent" :disabled="!canUndo" @click="toggle.undo">Undo</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent" :disabled="!canRedo" @click="toggle.redo">Redo</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-accent" @click="toggle.clear">Clear</button>
    </div>

    <!-- Editor -->
    <div class="rounded-md border bg-background">
      <EditorContent
        :editor="editor"
        class="prose dark:prose-invert max-w-none px-3 py-2 focus:outline-none"
        :class="[minHeightClass ?? 'min-h-48', maxHeightClass ?? 'max-h-[60vh] overflow-y-auto']"
      />
    </div>
  </div>
</template>

<style scoped>
.prose :deep(p.is-editor-empty:first-child::before) {
  color: hsl(var(--muted-foreground));
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
