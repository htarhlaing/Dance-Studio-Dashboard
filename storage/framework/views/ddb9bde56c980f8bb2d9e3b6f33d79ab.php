<?php $__env->startSection('title', 'Regular Classes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Regular Classes</h1>
            <div class="muted">Weekly schedule templates. Used by Availability as regular occupancy.</div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?php echo e(url('/availability')); ?>">Back to Availability</a>
            <a class="btn" href="<?php echo e(url('/regular-classes/create')); ?>">Create Regular Class</a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Day of Week</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Teacher</th>
                    <th>Class Type</th>
                    <th>Title</th>
                    <th style="width: 90px;">Active</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $regularClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $dayLabel = $dayNames[(int) $rc->day_of_week] ?? (string) $rc->day_of_week;
                        $start = is_string($rc->start_time) ? substr($rc->start_time, 0, 5) : '';
                        $end = is_string($rc->end_time) ? substr($rc->end_time, 0, 5) : '';
                    ?>
                    <tr>
                        <td><?php echo e($dayLabel); ?></td>
                        <td><?php echo e($start && $end ? ($start.'-'.$end) : ''); ?></td>
                        <td><?php echo e($rc->room?->name); ?></td>
                        <td><?php echo e($rc->teacher?->display_name ?? '-'); ?></td>
                        <td><?php echo e($rc->classType?->name); ?></td>
                        <td><?php echo e($rc->title); ?></td>
                        <td>
                            <span class="badge <?php echo e($rc->is_active ? 'status-confirmed' : 'status-cancelled'); ?>">
                                <?php echo e($rc->is_active ? 'Yes' : 'No'); ?>

                            </span>
                        </td>
                        <td>
                            <a class="btn btn-secondary btn-small" href="<?php echo e(url('/regular-classes/'.$rc->id.'/edit')); ?>">Edit</a>
                            <form method="post" action="<?php echo e(url('/regular-classes/'.$rc->id)); ?>" style="display: inline-block;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('delete'); ?>
                                <button class="btn btn-secondary btn-small" type="submit">Delete</button>
                            </form>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/regular-classes/index.blade.php ENDPATH**/ ?>