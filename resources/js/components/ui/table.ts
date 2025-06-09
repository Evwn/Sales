import { type VariantProps, cva } from 'class-variance-authority';
import { computed, defineComponent, h, type PropType } from 'vue';

export const Table = defineComponent({
    name: 'Table',
    setup(_, { slots }) {
        return () =>
            h('div', { class: 'relative w-full overflow-auto' }, [
                h('table', { class: 'min-w-full divide-y divide-gray-200' }, slots.default?.()),
            ]);
    },
});

export const TableHeader = defineComponent({
    name: 'TableHeader',
    setup(_, { slots }) {
        return () => h('thead', { class: 'bg-gray-100 border-b border-gray-200' }, [h('tr', {}, slots.default?.())]);
    },
});

export const TableBody = defineComponent({
    name: 'TableBody',
    setup(_, { slots }) {
        return () => h('tbody', { class: 'bg-white divide-y divide-gray-200' }, slots.default?.());
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
            h('tr', { class: 'hover:bg-gray-50' }, slots.default?.());
    },
});

export const TableHead = defineComponent({
    name: 'TableHead',
    props: {
        width: {
            type: String,
            default: 'w-1/6'
        }
    },
    setup(props, { slots }) {
        return () =>
            h(
                'th',
                {
                    class: `px-6 py-4 text-left text-sm font-semibold text-gray-900 bg-gray-100 border-b border-gray-200 ${props.width}`,
                    scope: 'col'
                },
                slots.default?.()
            );
    },
});

export const TableCell = defineComponent({
    name: 'TableCell',
    props: {
        width: {
            type: String,
            default: 'w-1/6'
        }
    },
    setup(props, { slots }) {
        return () =>
            h('td', { 
                class: `px-6 py-4 whitespace-nowrap text-sm text-gray-500 ${props.width}` 
            }, slots.default?.());
    },
});

export const TableCaption = defineComponent({
    name: 'TableCaption',
    setup(_, { slots }) {
        return () => h('caption', { class: 'mt-4 text-sm text-muted-foreground' }, slots.default?.());
    },
});