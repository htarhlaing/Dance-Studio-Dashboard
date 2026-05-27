<?php $__env->startSection('title', 'Attendance'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            .actions { display: flex; flex-wrap: wrap; gap: 6px; }
            .btn-present { background: #16a34a; border-color: #16a34a; }
            .btn-absent { background: #eab308; border-color: #eab308; color: #111827; }
            .btn-leave { background: #2563eb; border-color: #2563eb; }
            .btn-cancelled { background: #6b7280; border-color: #6b7280; }
            .btn-disabled { opacity: 0.5; cursor: not-allowed; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Attendance</h1>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings')); ?>">Private Bookings</a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="get" action="<?php echo e(url('/attendance')); ?>">
            <div class="form-row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="<?php echo e($date); ?>">
                </div>
                <div>
                    <button class="btn" type="submit">Search</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 120px;">Time</th>
                        <th>Room</th>
                        <th>Student</th>
                        <th>Teacher</th>
                        <th style="width: 140px;">Booking Status</th>
                        <th style="width: 320px;">Attendance Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = 'status-'.($booking->status ?? 'pending');
                            $start = $booking->start_at?->format('H:i');
                            $end = $booking->end_at?->format('H:i');
                            $isFinal = in_array($booking->status, ['completed', 'cancelled'], true);
                        ?>
                        <tr>
                            <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                            <td><?php echo e($booking->room?->name); ?></td>
                            <td><?php echo e($booking->student?->name); ?></td>
                            <td><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></td>
                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($booking->status); ?></span></td>
                            <td>
                                <?php if($isFinal): ?>
                                    <span class="muted">No actions available.</span>
                                <?php else: ?>
                                    <div class="actions">
                                        <?php $__currentLoopData = ['present', 'absent', 'leave', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $btnClass = match ($s) {
                                                    'present' => 'btn-present',
                                                    'absent' => 'btn-absent',
                                                    'leave' => 'btn-leave',
                                                    'cancelled' => 'btn-cancelled',
                                                    default => '',
                                                };
                                            ?>
                                            <form method="post" action="<?php echo e(url('/attendance/private-bookings/'.$booking->id.'/mark')); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="status" value="<?php echo e($s); ?>">
                                                <button class="btn btn-small <?php echo e($btnClass); ?>" type="submit"><?php echo e(ucfirst($s)); ?></button>
                                            </form>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="empty-state">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/attendance/index.blade.php ENDPATH**/ ?>