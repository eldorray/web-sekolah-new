<script lang="ts">
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogClose,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
    } from '@/components/ui/dialog';

    let {
        open = false,
        title = 'Hapus data ini?',
        description = 'Tindakan ini memindahkan data ke tempat sampah.',
        confirmLabel = 'Hapus',
        onConfirm,
        onOpenChange,
    }: {
        open?: boolean;
        title?: string;
        description?: string;
        confirmLabel?: string;
        onConfirm: () => void;
        onOpenChange: (value: boolean) => void;
    } = $props();
</script>

<Dialog {open} {onOpenChange}>
    <DialogContent>
        <DialogTitle>{title}</DialogTitle>
        <DialogDescription>{description}</DialogDescription>
        <DialogFooter class="gap-2">
            <DialogClose>
                <Button variant="secondary">Batal</Button>
            </DialogClose>
            <Button variant="destructive" onclick={onConfirm}>
                {confirmLabel}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
