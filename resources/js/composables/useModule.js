import { ref } from "vue";

const currentModule = ref("hr");

export function useModule() {
    return {
        currentModule,
    };
}
