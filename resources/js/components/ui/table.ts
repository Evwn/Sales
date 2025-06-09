import { type VariantProps, cva } from 'class-variance-authority';
import { computed, defineComponent, h, type PropType } from 'vue';

export const Table = defineComponent({
    name: 'Table',
    setup(_, { slots }) {
        return () =>
            h('div', { class: 'relative w-full overflow-auto' }, [
                h('table', { class: 'w-full caption-bottom text-sm' }, slots.default?.()),
            ]);
    },
});

export const TableHeader = defineComponent({
    name: 'TableHeader',
    setup(_, { slots }) {
        return () => h('thead', { class: 'border-b' }, [h('tr', {}, slots.default?.())]);
    },
});

export const TableBody = defineComponent({
    name: 'TableBody',
    setup(_, { slots }) {
        return () => h('tbody', { class: 'divide-y' }, slots.default?.());
    },
});

export const TableFooter = defineComponent({
    name: 'TableFooter',
    setup(_, { slots }) {
        return () =>
            h('tfoot', { class: 'border-t bg-muted/50 font-medium [&>tr]:last:border-b-0' }, slots.default?.());
    },
});

export const TableRow = defineComponent({
    name: 'TableRow',
    setup(_, { slots }) {
        return () =>
            h('tr', { class: 'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted' }, slots.default?.());
    },
});

export const TableHead = defineComponent({
    name: 'TableHead',
    setup(_, { slots }) {
        return () =>
            h(
                'th',
                {
                    class: 'h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0',
                },
                slots.default?.()
            );
    },
});

export const TableCell = defineComponent({
    name: 'TableCell',
    setup(_, { slots }) {
        return () =>
            h('td', { class: 'p-4 align-middle [&:has([role=checkbox])]:pr-0' }, slots.default?.());
    },
});

export const TableCaption = defineComponent({
    name: 'TableCaption',
    setup(_, { slots }) {
        return () => h('caption', { class: 'mt-4 text-sm text-muted-foreground' }, slots.default?.());
    },
});