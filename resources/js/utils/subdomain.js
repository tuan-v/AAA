export const getCurrentSubdomain = () => {
    const host = window.location.hostname.toLowerCase();

    if (
        host === "localhost" ||
        host === "::1" ||
        /^\d{1,3}(\.\d{1,3}){3}$/.test(host)
    ) {
        return "main";
    }

    const parts = host.split(".");
    return parts.length > 2 ? parts[0] : "main";
};
