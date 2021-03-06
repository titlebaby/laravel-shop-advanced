<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class CategoriesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品类目';

    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title)
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form(true)->edit($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
//        $grid = new Grid(new Category());
//
//        $grid->column('id', __('Id'));
//        $grid->column('name', __('Name'));
//        $grid->column('parent_id', __('Parent id'));
//        $grid->column('is_directory', __('Is directory'));
//        $grid->column('level', __('Level'));
//        $grid->column('path', __('Path'));
//        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));

        $grid = new Grid(new Category());
        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->level('层级');
        $grid->is_directory('是否目录')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->path('类目路径');
        $grid->actions(function ($actions) {
            //不展示laravel-admin 默认的查看按钮
            $actions->disableView();

        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('parent_id', __('Parent id'));
        $show->field('is_directory', __('Is directory'));
        $show->field('level', __('Level'));
        $show->field('path', __('Path'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($isEditing = false)
    {
        $form = new Form(new Category());

        $form->text('name', '类目名称')->rules('required');
        //如果是编辑的情况
        if ($isEditing) {
            //不允许用户修改【是否目录】和【父目录】字段值
            //用display()方法来展示值，with()方法接受一个匿名函数，会把字段值传给匿名函数并把返回值展示出来
            $form->display('is_directory', '是否目录')->with(function ($value) {
                return $value ? '是' : '否';
            });
            //支持用符号，来展示关联关系的字段
            $form->display('parent.name', '父目录');
        } else {
            //定义一个名为【是否目录】的单选框
            $form->radio('is_directory', '是否目录')
                ->options(['1' => '是', '0' => '否'])
                ->default('0')
                ->rules('required');
            //定义一个名为父类目的下拉框
            $form->select('parent_id', '父类目')->ajax('/admin/api/categories');
        }


//        $form->text('name', __('Name'));
//        $form->number('parent_id', __('Parent id'));
//        $form->switch('is_directory', __('Is directory'));
//        $form->number('level', __('Level'));
//        $form->text('path', __('Path'));

        return $form;
    }

    //定义下拉框搜索接口
    public function apiIndex(Request $request)
    {
        //用户输入的值通过q参数获取
        $search = $request->input('q');
        $result = Category::query()
            ->where('is_directory',  boolval($request->input('is_directory', true)))
            ->where('name', 'like', '%' . $search . '%')
            ->paginate();
        //把查询出来的结果重新组装成laravel-admin 需要的格式
        $result->setCollection($result->getCollection()->map(function (Category $category) {
            return ['id' => $category->id, 'text' => $category->full_name];
        }));
        return $result;

    }
}
