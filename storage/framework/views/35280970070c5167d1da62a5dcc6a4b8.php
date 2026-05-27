<?php $__env->startSection('title', 'Studio Settings'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .settings-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 14px; }
        .settings-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
        .settings-card-title { font-weight: 800; margin: 0 0 6px; color: #111827; }
        .settings-card-desc { margin: 0 0 10px; color: #6b7280; font-size: 12px; line-height: 1.4; }
        .settings-list { margin: 0; padding-left: 18px; color: #111827; font-size: 12px; line-height: 1.5; }
        .settings-list li { margin: 0 0 4px; }
        .settings-actions { margin-top: 12px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        @media (max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Studio Settings</h1>
            <div class="muted" style="margin-top: 4px;">
                <?php echo e($studio->name); ?>

            </div>
            <div class="muted" style="margin-top: 6px;">
                Manage this studio's schedule, booking rules, rooms, class types, and package settings.
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <div class="settings-card">
            <div class="settings-card-title">Business Hours</div>
            <p class="settings-card-desc">Set opening time, closing time, slot interval, and default private lesson duration.</p>
            <ul class="settings-list">
                <li>Business hours: <?php echo e($studio->business_start_time ? substr((string) $studio->business_start_time, 0, 5) : '-'); ?> - <?php echo e($studio->business_end_time ? substr((string) $studio->business_end_time, 0, 5) : '-'); ?></li>
                <li>Slot interval: <?php echo e((int) ($studio->booking_interval_minutes ?? 0) > 0 ? (int) $studio->booking_interval_minutes : '-'); ?> minutes</li>
                <li>Default private lesson duration: <?php echo e((int) ($privateLessonClassType?->default_duration_minutes ?? 0) > 0 ? (int) $privateLessonClassType->default_duration_minutes : '-'); ?> minutes</li>
            </ul>
            <div class="settings-actions">
                <a class="btn" href="<?php echo e(url('/studio-settings/edit')); ?>#business-hours">Edit Business Hours</a>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-title">Booking Rules</div>
            <p class="settings-card-desc">Set how many hours in advance bookings, reschedules, and cancellations are allowed.</p>
            <ul class="settings-list">
                <li>Minimum booking notice: <?php echo e((int) ($studio->minimum_booking_notice_hours ?? 48)); ?> hours</li>
                <li>Minimum reschedule notice: <?php echo e((int) ($studio->minimum_reschedule_notice_hours ?? 48)); ?> hours</li>
                <li>Minimum cancel notice: <?php echo e((int) ($studio->minimum_cancel_notice_hours ?? 24)); ?> hours</li>
                <li>Admin/front desk override: <?php echo e(($studio->allow_admin_frontdesk_override_notice ?? true) ? 'Yes' : 'No'); ?></li>
                <li>Teacher can create booking: <?php echo e(($studio->teacher_can_create_booking ?? false) ? 'Yes' : 'No'); ?></li>
            </ul>
            <div class="settings-actions">
                <a class="btn" href="<?php echo e(url('/studio-settings/edit')); ?>#booking-rules">Edit Booking Rules</a>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-title">Rooms</div>
            <p class="settings-card-desc">Manage rooms/studios used for classes and private lessons.</p>
            <ul class="settings-list">
                <li>Active rooms count: <?php echo e($activeRoomsCount); ?></li>
            </ul>
            <div class="settings-actions">
                <a class="btn" href="<?php echo e(url('/rooms')); ?>">Manage Rooms</a>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-title">Class Types</div>
            <p class="settings-card-desc">Manage dance or class types such as K-pop, Hip-hop, Beginner, etc.</p>
            <ul class="settings-list">
                <li>Active class types count: <?php echo e($activeClassTypesCount); ?></li>
            </ul>
            <div class="settings-actions">
                <a class="btn" href="<?php echo e(url('/class-types')); ?>">Manage Class Types</a>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-title">Package Types</div>
            <p class="settings-card-desc">Manage lesson package types and pricing rules.</p>
            <ul class="settings-list">
                <li>Active package types count: <?php echo e($activePackageTypesCount); ?></li>
            </ul>
            <div class="settings-actions">
                <a class="btn" href="<?php echo e(url('/package-types')); ?>">Manage Package Types</a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/studio-settings/index.blade.php ENDPATH**/ ?>