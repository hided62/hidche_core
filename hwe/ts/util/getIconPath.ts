export function getIconPath(imgsvr: boolean | 1 | 0, picture: string): string {
    // ../d_shared/common_path.js 필요
    if (!imgsvr) {
        return `${window.pathConfig.sharedIcon}/${picture}`;
    } else {
        return `${window.pathConfig.root}/d_pic/${picture}`;
    }
}
