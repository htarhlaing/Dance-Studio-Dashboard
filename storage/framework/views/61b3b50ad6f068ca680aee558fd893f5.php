<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin-top: 0;">Dashboard</h1>
            <div class="muted">Today: <?php echo e($today->format('Y-m-d')); ?></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <p class="stat-label">Today Private Bookings</p>
            <p class="stat-value"><?php echo e($todayPrivateBookingsCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending Bookings</p>
            <p class="stat-value"><?php echo e($pendingBookingsCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Completed Today</p>
            <p class="stat-value"><?php echo e($completedTodayCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Low Packages</p>
            <p class="stat-value"><?php echo e($lowPackagesCount); ?></p>
        </div>
    </div>

    <h2 style="margin: 0 0 10px;">Today Private Bookings</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $todayPrivateBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusValue = $booking->status ?? '';
                        $statusClass = 'status-'.($statusValue ?: 'pending');
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    ?>
                    <tr>
                        <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                        <td><?php echo e($booking->room?->name); ?></td>
                        <td><?php echo e($booking->student?->name); ?></td>
                        <td><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></td>
                        <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusValue); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="empty-state">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2 style="margin: 18px 0 10px;">Pending Bookings</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $pendingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    ?>
                    <tr>
                        <td><?php echo e($booking->start_at?->format('Y-m-d')); ?></td>
                        <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                        <td><?php echo e($booking->room?->name); ?></td>
                        <td><?php echo e($booking->student?->name); ?></td>
                        <td><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="empty-state">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>

    <h2 style="margin: 18px 0 10px;">Low Remaining Packages</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Package Type</th>
                    <th style="width: 160px;">Remaining Lessons</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $lowRemainingPackages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $remaining = (int) ($pkg->remaining_units ?? 0);
                        $remainingClass = $remaining <= 0 ? 'remaining-empty' : 'remaining-warn';
                    ?>
                    <tr>
                        <td><?php echo e($pkg->student?->name); ?></td>
                        <td><?php echo e($pkg->packageType?->name); ?></td>
                        <td><span class="badge <?php echo e($remainingClass); ?>"><?php echo e($remaining); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="empty-state">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/dashboard.blade.php ENDPATH**/ ?>