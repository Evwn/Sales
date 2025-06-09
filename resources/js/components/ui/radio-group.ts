import { defineComponent, h, provide, ref, inject, computed } from 'vue';
import { cn } from '@/lib/utils';

const RadioGroup = defineComponent({
    name: 'RadioGroup',
    props: {
        modelValue: {
            type: [String, Number, Boolean],
            default: undefined,
        },
        class: {
            type: String,
            default: '',
        },
    },
    emits: ['update:modelValue'],
    setup(props, { slots, emit }) {
        const value = ref(props.modelValue);
        provide('radio-group', {
            value,
            onValueChange: (newValue: any) => {
                value.value = newValue;
                emit('update:modelValue', newValue);
            },
        });

        return () => h('div', { class: cn('grid gap-2', props.class) }, slots.default?.());
    },
});

const RadioGroupItem = defineComponent({
    name: 'RadioGroupItem',
    props: {
        value: {
            type: [String, Number, Boolean],
            required: true,
        },
        id: {
            type: String,
            required: true,
        },
        class: {
            type: String,
            default: '',
        },
    },
    setup(props, { slots }) {
        const radioGroup = inject('radio-group');
        const isChecked = computed(() => radioGroup?.value === props.value);

        return () => h('input', {
            type: 'radio',
            id: props.id,
            value: props.value,
            checked: isChecked.value,
            class: cn(props.class),
            onChange: (e: Event) => {
                const target = e.target as HTMLInputElement;
                radioGroup?.onValueChange(target.value);
            },
        });
    },
});

export { RadioGroup, RadioGroupItem }; 