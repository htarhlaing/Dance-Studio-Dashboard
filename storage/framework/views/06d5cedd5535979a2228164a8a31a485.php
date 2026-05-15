<?php $__env->startSection('title', 'Private Bookings'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background: #f8fafc; }
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input, select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
            button, .btn { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn-secondary { background: #fff; color: #111827; }
            .muted { color: #6b7280; font-size: 12px; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #111827; background: #e5e7eb; }
            .status-pending { background: #fef3c7; color: #92400e; }
            .status-confirmed { background: #dbeafe; color: #1e40af; }
            .status-completed { background: #d1fae5; color: #065f46; }
            .status-cancelled { background: #e5e7eb; color: #374151; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="row" style="justify-content: space-between;">
            <div>
                <h1 style="margin: 0;">Private Bookings</h1>
            </div>
            <div class="row" style="margin: 0;">
                <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
                <a class="btn" href="<?php echo e(url('/private-bookings/create')); ?>">Create Booking</a>
            </div>
        </div>

        <form method="get" action="<?php echo e(url('/private-bookings')); ?>">
            <div class="row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="<?php echo e($date); ?>">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        <?php $__currentLoopData = ['pending', 'confirmed', 'completed', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>" <?php if($status === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Filter</button>
                </div>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 170px;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusClass = 'status-'.($booking->status ?? 'pending');
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    ?>
                    <tr>
                        <td><?php echo e($booking->start_at?->format('Y-m-d')); ?></td>
                        <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                        <td><?php echo e($booking->room?->name); ?></td>
                        <td><?php echo e($booking->student?->name); ?></td>
                        <td><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></td>
                        <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($booking->status); ?></span></td>
                        <td class="muted"><?php echo e($booking->created_at?->format('Y-m-d H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="muted">No private bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/private-bookings/index.blade.php ENDPATH**/ ?>