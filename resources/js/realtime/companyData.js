import { getCurrentSubdomain } from "@/utils/subdomain";

let companyDataChannel = null;
let setupRetryTimer = null;
let setupAttempts = 0;
const MAX_SETUP_ATTEMPTS = 20;

export const setupCompanyDataRealtime = (user) => {
    if (!user?.company_id || companyDataChannel) return;

    if (!window.Echo) {
        if (setupAttempts >= MAX_SETUP_ATTEMPTS || setupRetryTimer) return;

        setupAttempts++;
        setupRetryTimer = window.setTimeout(() => {
            setupRetryTimer = null;
            setupCompanyDataRealtime(user);
        }, 500);
        return;
    }

    setupAttempts = 0;

    const channelName = `company.${user.company_id}.${getCurrentSubdomain()}.data`;
    companyDataChannel = window.Echo.private(channelName)
        .listen(".company.data.changed", (event) => {
            window.dispatchEvent(
                new CustomEvent("company-data-changed", { detail: event }),
            );
        })
        .error((error) => {
            console.error("Company data realtime subscription failed:", error);
        });
};
