<?php $__env->startSection('title', 'Weekly Schedule'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .weekly-grid { overflow-x: auto; }
        .schedule-cell { min-width: 190px; }
        .schedule-empty { color: #9ca3af; font-size: 12px; }

        .schedule-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px 10px; background: #ffffff; margin: 0 0 8px; }
        .schedule-card:last-child { margin-bottom: 0; }
        .schedule-card-header { display: flex; gap: 8px; align-items: baseline; margin-bottom: 6px; }
        .schedule-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .schedule-badge-regular { background: #dbeafe; color: #1e40af; }
        .schedule-badge-private { background: #ffedd5; color: #9a3412; }
        .schedule-title { font-weight: 700; color: #111827; font-size: 13px; line-height: 1.25; }
        .schedule-line { color: #111827; font-size: 12px; line-height: 1.35; }
        .schedule-meta { color: #6b7280; font-size: 12px; line-height: 1.35; margin-top: 2px; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Weekly Schedule</h1>
            <div class="muted">Week: <?php echo e($weekStart->toDateString()); ?> - <?php echo e($weekStart->addDays(6)->toDateString()); ?></div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?php echo e(url('/dashboard')); ?>">Dashboard</a>
            <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Availability</a>
            <a class="btn btn-secondary" href="<?php echo e(url('/regular-classes')); ?>">Regular Classes</a>
            <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings')); ?>">Private Bookings</a>
        </div>
    </div>

    <form method="get" action="<?php echo e(url('/weekly-schedule')); ?>">
        <div class="form-row">
            <div>
                <label for="week_start">Week Start (Monday)</label>
                <input id="week_start" name="week_start" type="date" value="<?php echo e($weekStart->toDateString()); ?>">
            </div>
            <div>
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id">
                    <option value="">All Rooms</option>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($room->id); ?>" <?php if((string) $roomId === (string) $room->id): echo 'selected'; endif; ?>><?php echo e($room->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <button class="btn" type="submit">Go</button>
            </div>
        </div>
    </form>

    <div class="table-wrap weekly-grid">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 110px;">Time</th>
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th>
                            <?php echo e($day['date']->format('l')); ?>

                            <div class="muted"><?php echo e($day['date']->format('Y-m-d')); ?></div>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slotIndex => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="muted"><?php echo e($slot['label']); ?></td>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $items = $grid[$slotIndex][$dayIndex] ?? [];
                            ?>
                            <td class="schedule-cell">
                                <?php if(count($items) === 0): ?>
                                    <span class="schedule-empty">-</span>
                                <?php else: ?>
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make('dance-studio.weekly-schedule.partials.schedule-card', ['item' => $item], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="empty-state">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/weekly-schedule/index.blade.php ENDPATH**/ ?>