<?php $__env->startSection('title', 'Student Packages'); ?>

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
            .status-active { background: #d1fae5; color: #065f46; }
            .status-expired { background: #e5e7eb; color: #374151; }
            .status-void { background: #fee2e2; color: #991b1b; }
            .status-used_up { background: #fee2e2; color: #991b1b; }
            .remaining-ok { color: #111827; }
            .remaining-warn { color: #92400e; background: #fef3c7; padding: 2px 6px; border-radius: 6px; }
            .remaining-empty { color: #991b1b; background: #fee2e2; padding: 2px 6px; border-radius: 6px; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="row" style="justify-content: space-between;">
            <div>
                <h1 style="margin: 0;">Student Packages</h1>
            </div>
            <div class="row" style="margin: 0;">
                <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings')); ?>">Private Bookings</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/attendance')); ?>">Attendance</a>
            </div>
        </div>

        <form method="get" action="<?php echo e(url('/student-packages')); ?>">
            <div class="row">
                <div>
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id">
                        <option value="">All</option>
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>" <?php if($studentId === $student->id): echo 'selected'; endif; ?>><?php echo e($student->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        <?php $__currentLoopData = $availableStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                    <th>Student</th>
                    <th>Package Type</th>
                    <th style="width: 120px;">Total Lessons</th>
                    <th style="width: 120px;">Used Lessons</th>
                    <th style="width: 150px;">Remaining Lessons</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 170px;">Purchased At</th>
                    <th style="width: 170px;">Expires At</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $studentPackages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $total = (int) ($pkg->total_units ?? 0);
                        $remaining = (int) ($pkg->remaining_units ?? 0);
                        $used = max(0, $total - $remaining);

                        $statusValue = (string) ($pkg->status ?? '');
                        $statusClass = 'status-'.str_replace('-', '_', $statusValue);

                        $remainingClass = 'remaining-ok';
                        $remainingLabel = (string) $remaining;
                        if ($remaining <= 0) {
                            $remainingClass = 'remaining-empty';
                            $remainingLabel = '0 (Need Renewal)';
                        } elseif ($remaining <= 3) {
                            $remainingClass = 'remaining-warn';
                        }
                    ?>
                    <tr>
                        <td><?php echo e($pkg->student?->name); ?></td>
                        <td><?php echo e($pkg->packageType?->name); ?></td>
                        <td><?php echo e($total); ?></td>
                        <td><?php echo e($used); ?></td>
                        <td><span class="<?php echo e($remainingClass); ?>"><?php echo e($remainingLabel); ?></span></td>
                        <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusValue); ?></span></td>
                        <td class="muted"><?php echo e($pkg->purchased_at?->format('Y-m-d H:i') ?? $pkg->created_at?->format('Y-m-d H:i')); ?></td>
                        <td class="muted"><?php echo e($pkg->expires_at?->format('Y-m-d H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="muted">No student packages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/student-packages/index.blade.php ENDPATH**/ ?>