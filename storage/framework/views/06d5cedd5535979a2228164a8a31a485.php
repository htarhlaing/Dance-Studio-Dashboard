<?php $__env->startSection('title', 'Private Bookings'); ?>

<?php $__env->startSection('content'); ?>
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Private Bookings</h1>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
                <?php if($canSeeCreateEntry): ?>
                    <a class="btn" href="<?php echo e($findAvailabilityUrl); ?>">Find Available Slot</a>
                    <?php if($showManualCreate): ?>
                        <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings/create')); ?>">Manual Create</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <form method="get" action="<?php echo e(url('/private-bookings')); ?>">
            <div class="form-row">
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
                    <button class="btn" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 120px;">Time</th>
                        <th>Room</th>
                        <th>Student</th>
                        <th>Teacher</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 170px;">Created At</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = 'status-'.($booking->status ?? 'pending');
                            $start = $booking->start_at?->format('H:i');
                            $end = $booking->end_at?->format('H:i');
                            $now = now();
                            $displayStatus = 'Upcoming';
                            if (($booking->status ?? '') === 'cancelled') {
                                $displayStatus = 'Cancelled';
                            } elseif (($booking->status ?? '') === 'completed') {
                                $displayStatus = 'Completed';
                            } else {
                                if ($booking->start_at !== null && $booking->end_at !== null) {
                                    if ($now->lt($booking->start_at)) {
                                        $displayStatus = 'Upcoming';
                                    } elseif ($now->gte($booking->start_at) && $now->lte($booking->end_at)) {
                                        $displayStatus = 'Ongoing';
                                    } elseif ($now->gt($booking->end_at)) {
                                        $displayStatus = 'Pending Attendance';
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo e($booking->start_at?->format('Y-m-d')); ?></td>
                            <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                            <td><?php echo e($booking->room?->name); ?></td>
                            <td><?php echo e($booking->student?->name); ?></td>
                            <td><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></td>
                            <td>
                                <span class="badge"><?php echo e($displayStatus); ?></span>
                                <div class="muted"><?php echo e($booking->status); ?></div>
                            </td>
                            <td class="muted"><?php echo e($booking->created_at?->format('Y-m-d H:i')); ?></td>
                            <td>
                                <?php if(in_array($booking->status, ['pending', 'confirmed'], true)): ?>
                                    <div class="actions">
                                        <a class="btn btn-secondary btn-small" href="<?php echo e(url('/private-bookings/'.$booking->id.'/reschedule')); ?>">Reschedule</a>
                                        <form method="post" action="<?php echo e(url('/private-bookings/'.$booking->id.'/cancel')); ?>" style="display: inline-block;">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-secondary btn-small" type="submit">Cancel</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="muted">-</span>
                                <?php endif; ?>
                            </td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/private-bookings/index.blade.php ENDPATH**/ ?>