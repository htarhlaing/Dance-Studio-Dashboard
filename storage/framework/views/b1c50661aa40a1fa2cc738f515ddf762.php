<?php $__env->startSection('title', 'Package Types'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Package Types</h1>
        </div>
        <div class="page-actions">
            <a class="btn" href="<?php echo e(url('/package-types/create')); ?>">Create Package Type</a>
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

    <form method="get" action="<?php echo e(url('/package-types')); ?>">
        <div class="form-row">
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?php echo e($name); ?>">
            </div>
            <div>
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="">All</option>
                    <option value="1" <?php if($isActive === true): echo 'selected'; endif; ?>>Active</option>
                    <option value="0" <?php if($isActive === false): echo 'selected'; endif; ?>>Inactive</option>
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
                    <th>Name</th>
                    <th style="width: 120px;">Lessons</th>
                    <th style="width: 140px;">Validity Days</th>
                    <th style="width: 130px;">Price</th>
                    <th style="width: 100px;">Currency</th>
                    <th style="width: 90px;">Active</th>
                    <th style="width: 170px;">Created At</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $packageTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $packageType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($packageType->name); ?></td>
                        <td class="muted"><?php echo e($packageType->lessons_count); ?></td>
                        <td class="muted"><?php echo e($packageType->validity_days); ?></td>
                        <td class="muted"><?php echo e($packageType->price); ?></td>
                        <td class="muted"><?php echo e($packageType->currency); ?></td>
                        <td>
                            <span class="badge <?php echo e($packageType->is_active ? 'status-confirmed' : 'status-cancelled'); ?>">
                                <?php echo e($packageType->is_active ? 'Yes' : 'No'); ?>

                            </span>
                        </td>
                        <td class="muted"><?php echo e($packageType->created_at?->format('Y-m-d H:i')); ?></td>
                        <td>
                            <a class="btn btn-secondary btn-small" href="<?php echo e(url('/package-types/'.$packageType->id.'/edit')); ?>">Edit</a>
                            <form method="post" action="<?php echo e(url('/package-types/'.$packageType->id)); ?>" style="display: inline-block;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('delete'); ?>
                                <button class="btn btn-secondary btn-small" type="submit">Deactivate</button>
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


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/package-types/index.blade.php ENDPATH**/ ?>