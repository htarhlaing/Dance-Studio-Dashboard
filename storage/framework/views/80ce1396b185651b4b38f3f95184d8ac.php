<?php $__env->startSection('title', 'Student Packages'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            .status-active { background: #d1fae5; color: #065f46; }
            .status-expired { background: #e5e7eb; color: #374151; }
            .status-void { background: #fee2e2; color: #991b1b; }
            .status-used_up { background: #fee2e2; color: #991b1b; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Student Packages</h1>
            </div>
            <div class="page-actions">
                <a class="btn" href="<?php echo e(url('/student-packages/create')); ?>">Create / Renew Package</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings')); ?>">Private Bookings</a>
                <a class="btn btn-secondary" href="<?php echo e(url('/attendance')); ?>">Attendance</a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="get" action="<?php echo e(url('/student-packages')); ?>">
            <div class="form-row">
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
                    <button class="btn" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
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

                            $remainingClass = '';
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
                            <td><span class="badge <?php echo e($remainingClass); ?>"><?php echo e($remainingLabel); ?></span></td>
                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusValue); ?></span></td>
                            <td class="muted"><?php echo e($pkg->purchased_at?->format('Y-m-d H:i') ?? $pkg->created_at?->format('Y-m-d H:i')); ?></td>
                            <td class="muted"><?php echo e($pkg->expires_at?->format('Y-m-d H:i')); ?></td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/student-packages/index.blade.php ENDPATH**/ ?>