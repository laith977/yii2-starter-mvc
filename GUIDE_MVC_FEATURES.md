# Complete Guide: Building MVC Features in Yii2

> **A comprehensive, step-by-step guide to building complete MVC features in Yii2 Framework**

This guide provides everything you need to create production-ready MVC features in Yii2, from database migrations to user interface. Follow along to build a complete Product Management system as a working example.

## 📖 About This Guide

This guide is designed for developers who want to:
- Understand how MVC works in Yii2
- Build complete CRUD (Create, Read, Update, Delete) features
- Implement search, filtering, and pagination
- Handle file uploads and form validation
- Follow Yii2 best practices

**Prerequisites:**
- Basic PHP knowledge
- Understanding of MVC pattern
- Yii2 application set up (see [README.md](README.md) for setup instructions)
- Database configured and accessible

**What You'll Build:**
A complete Product Management system with:
- Database tables (products, categories)
- Models with validation and relationships
- Controllers with full CRUD operations
- Views with forms, lists, and detail pages
- Search and filtering capabilities
- File upload functionality

## Table of Contents

1. [Understanding MVC in Yii2](#understanding-mvc-in-yii2)
2. [Creating a Complete Feature: Step-by-Step](#creating-a-complete-feature-step-by-step)
3. [Database Layer (Model)](#database-layer-model)
4. [Business Logic Layer (Controller)](#business-logic-layer-controller)
5. [Presentation Layer (View)](#presentation-layer-view)
6. [Required Configuration Setup](#required-configuration-setup)
7. [How Yii2 Connects Your MVC (Automatic Routing)](#how-yii2-connects-your-mvc-automatic-routing)
8. [Next Steps After Implementation](#next-steps-after-implementation)
9. [Form Handling](#form-handling)
10. [Validation](#validation)
11. [Relationships](#relationships)
12. [Search and Filtering](#search-and-filtering)
13. [Pagination](#pagination)
14. [AJAX Operations](#ajax-operations)
15. [File Uploads](#file-uploads)
16. [Best Practices](#best-practices)
17. [Troubleshooting](#troubleshooting)
18. [Summary](#summary)
19. [Quick Reference: Essential Setup Checklist](#quick-reference-essential-setup-checklist)
20. [Complete File Checklist](#complete-file-checklist)

---

## Understanding MVC in Yii2

### Model (M)
- Represents data and business logic
- Extends `yii\db\ActiveRecord` for database models
- Handles data validation, relationships, and queries
- Located in `models/` directory

### View (V)
- Represents the presentation layer
- Renders HTML output
- Located in `views/` directory (organized by controller)

### Controller (C)
- Handles user requests
- Processes input data
- Calls models for business logic
- Renders views
- Located in `controllers/` directory

---

## Creating a Complete Feature: Step-by-Step

We'll create a **Product Management** feature as a complete example.

### Step 1: Create Database Migration

```bash
php yii migrate/create create_products_table
```

Edit the migration file. **Important:** The filename will be something like `m260105_225305_create_products_table.php` (with a timestamp). Use the exact class name that matches your filename:

```php
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%products}}`.
 */
class m260105_225305_create_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'price' => $this->decimal(10, 2)->notNull(),
            'stock' => $this->integer()->defaultValue(0),
            'category_id' => $this->integer(),
            'status' => $this->tinyInteger()->defaultValue(1)->comment('1=Active, 0=Inactive'),
            'image' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for category_id
        $this->createIndex(
            'idx-products-category_id',
            '{{%products}}',
            'category_id'
        );

        // Create foreign key (if categories table exists)
        // $this->addForeignKey(
        //     'fk-products-category_id',
        //     '{{%products}}',
        //     'category_id',
        //     '{{%categories}}',
        //     'id',
        //     'CASCADE'
        // );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%products}}');
    }
}
```

**Important:** The migration class name MUST match the filename. When you run `php yii migrate/create create_products_table`, Yii2 automatically generates a filename like `m260105_225305_create_products_table.php` with a matching class name. Always use the generated class name - do not change it manually.

Run the migration:
```bash
php yii migrate
```

**Note:** This example references a `categories` table for the `category_id` foreign key. You'll need to create the Category model and migration (see "Creating Related Models" section below) before uncommenting the foreign key constraint.

---

## Database Layer (Model)

### Creating the Model

Create `models/Product.php`:

```php
<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use app\models\Category;

/**
 * Product Model
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $stock
 * @property int|null $category_id
 * @property int $status
 * @property string|null $image
 * @property int $created_at
 * @property int $updated_at
 */
class Product extends ActiveRecord
{
    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%products}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'price'], 'required'],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['stock', 'category_id', 'status'], 'integer'],
            [['stock'], 'integer', 'min' => 0],
            [['name'], 'string', 'max' => 255],
            [['image'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['category_id'], 'exist', 'skipOnError' => true, 
                'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Product Name',
            'description' => 'Description',
            'price' => 'Price',
            'stock' => 'Stock Quantity',
            'category_id' => 'Category',
            'status' => 'Status',
            'image' => 'Product Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return $this->status === self::STATUS_ACTIVE ? 'Active' : 'Inactive';
    }

    /**
     * Get category relationship
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock()
    {
        return $this->stock > 0;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice()
    {
        return Yii::$app->formatter->asCurrency($this->price);
    }
}
```

### Creating Search Model (for GridView/ListView)

Create `models/ProductSearch.php`:

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductSearch represents the model behind the search form.
 */
class ProductSearch extends Product
{
    public $categoryName; // For searching by category name

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'stock', 'category_id', 'status'], 'integer'],
            [['name', 'description', 'categoryName'], 'safe'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Product::find();

        // Join with category table if needed
        // $query->joinWith(['category']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'price' => $this->price,
            'stock' => $this->stock,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
```

---

### Creating Related Models (Category Example)

Since the Product model references a `Category` model, you'll need to create it as well.

#### Step 1: Create Category Migration

```bash
php yii migrate/create create_categories_table
```

Edit the migration file:

```php
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%categories}}`.
 */
class m260105_225306_create_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'status' => $this->tinyInteger()->defaultValue(1)->comment('1=Active, 0=Inactive'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for status
        $this->createIndex(
            'idx-categories-status',
            '{{%categories}}',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%categories}}');
    }
}
```

#### Step 2: Create Category Model

Create `models/Category.php`:

```php
<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Category Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Category extends ActiveRecord
{
    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Category Name',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return $this->status === self::STATUS_ACTIVE ? 'Active' : 'Inactive';
    }

    /**
     * Get products relationship
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['category_id' => 'id']);
    }
}
```

**Note:** The Product form will work even if no categories exist - the category dropdown will simply be empty. The `category_id` field is optional.

---

## Business Logic Layer (Controller)

### Creating the Controller

Create `controllers/ProductController.php`:

```php
<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\ProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            // Uncomment to add access control
            // 'access' => [
            //     'class' => AccessControl::class,
            //     'rules' => [
            //         [
            //             'allow' => true,
            //             'roles' => ['@'], // Only authenticated users
            //         ],
            //     ],
            // ],
        ];
    }

    /**
     * Lists all Product models.
     * @return string
     */
    public function actionIndex()
    {
        // Optional: Check if table exists (prevents errors if migrations haven't been run)
        $tableName = Product::tableName();
        $tableSchema = Yii::$app->db->getTableSchema($tableName, true);
        
        if ($tableSchema === null) {
            Yii::$app->session->setFlash('error', 'Database table "' . $tableName . '" does not exist. Please run migrations: php yii migrate');
            // Return empty data provider instead of crashing
            $searchModel = new ProductSearch();
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => Product::find()->where('1=0'), // Empty query
                'pagination' => false,
            ]);
            
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post())) {
            // Handle file upload
            $image = UploadedFile::getInstance($model, 'image');
            if ($image) {
                $model->image = $this->uploadImage($image);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Product created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldImage = $model->image;

        if ($model->load(Yii::$app->request->post())) {
            // Handle file upload
            $image = UploadedFile::getInstance($model, 'image');
            if ($image) {
                // Delete old image
                if ($oldImage && file_exists(Yii::getAlias('@webroot/uploads/products/') . $oldImage)) {
                    unlink(Yii::getAlias('@webroot/uploads/products/') . $oldImage);
                }
                $model->image = $this->uploadImage($image);
            } else {
                $model->image = $oldImage;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Product updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Delete associated image
        if ($model->image && file_exists(Yii::getAlias('@webroot/uploads/products/') . $model->image)) {
            unlink(Yii::getAlias('@webroot/uploads/products/') . $model->image);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Product deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Upload image file
     * @param UploadedFile $file
     * @return string|null
     */
    protected function uploadImage($file)
    {
        $uploadPath = Yii::getAlias('@webroot/uploads/products/');
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
        $filePath = $uploadPath . $fileName;

        if ($file->saveAs($filePath)) {
            return $fileName;
        }

        return null;
    }
}
```

---

## Presentation Layer (View)

### Index View (List with Search)

Create `views/product/index.php`:

```php
<?php

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Product;

/** @var yii\web\View $this */
/** @var app\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Product', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'price',
                'value' => function ($model) {
                    return $model->getFormattedPrice();
                },
            ],
            'stock',
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return $model->category ? $model->category->name : 'N/A';
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
                'filter' => [
                    Product::STATUS_ACTIVE => 'Active',
                    Product::STATUS_INACTIVE => 'Inactive',
                ],
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::beginForm(['delete', 'id' => $model->id], 'post', [
                            'style' => 'display: inline-block;',
                        ]) .
                        Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) .
                        Html::submitButton('Delete', [
                            'class' => 'btn btn-link',
                            'style' => 'padding: 0; border: none; background: none; color: #d9534f; text-decoration: underline;',
                            'onclick' => 'return confirm("Are you sure you want to delete this item?");',
                            'title' => 'Delete',
                        ]) .
                        Html::endForm();
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
```

### View (Detail Page)

Create `views/product/view.php`:

```php
<?php

use Yii;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::beginForm(['delete', 'id' => $model->id], 'post', [
            'style' => 'display: inline-block;',
        ]) ?>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <?= Html::submitButton('Delete', [
            'class' => 'btn btn-danger',
            'onclick' => 'return confirm("Are you sure you want to delete this item?");',
        ]) ?>
        <?= Html::endForm() ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'price',
                'value' => $model->getFormattedPrice(),
            ],
            'stock',
            [
                'attribute' => 'category_id',
                'value' => $model->category ? $model->category->name : 'N/A',
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatusLabel(),
            ],
            [
                'attribute' => 'image',
                'format' => 'raw',
                'value' => $model->image 
                    ? Html::img('/uploads/products/' . $model->image, ['style' => 'max-width: 200px;'])
                    : 'No image',
            ],
            [
                'attribute' => 'created_at',
                'value' => Yii::$app->formatter->asDatetime($model->created_at),
            ],
            [
                'attribute' => 'updated_at',
                'value' => Yii::$app->formatter->asDatetime($model->updated_at),
            ],
        ],
    ]) ?>

</div>
```

### Create/Update Form

Create `views/product/_form.php`:

```php
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Category;
use app\models\Product;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'], // Important for file uploads
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01']) ?>

    <?= $form->field($model, 'stock')->textInput(['type' => 'number']) ?>

    <?= $form->field($model, 'category_id')->dropDownList(
        Category::find()->exists() 
            ? ArrayHelper::map(Category::find()->where(['status' => Category::STATUS_ACTIVE])->all(), 'id', 'name')
            : [],
        ['prompt' => 'Select Category (Optional)']
    ) ?>

    <?= $form->field($model, 'status')->dropDownList([
        Product::STATUS_ACTIVE => 'Active',
        Product::STATUS_INACTIVE => 'Inactive',
    ]) ?>

    <?= $form->field($model, 'image')->fileInput() ?>
    
    <?php if ($model->image): ?>
        <div class="form-group">
            <?= Html::img('/uploads/products/' . $model->image, ['style' => 'max-width: 200px;']) ?>
            <p>Current image</p>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
```

Create `views/product/create.php`:

```php
<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = 'Create Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
```

Create `views/product/update.php`:

```php
<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = 'Update Product: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
```

---

## Form Handling

### Handling GET Requests

```php
public function actionCreate()
{
    $model = new Product();
    
    // GET request - show form
    if (Yii::$app->request->isGet) {
        return $this->render('create', ['model' => $model]);
    }
    
    // POST request - process form
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    return $this->render('create', ['model' => $model]);
}
```

### Handling AJAX Requests

```php
public function actionCreate()
{
    $model = new Product();
    
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if ($model->save()) {
            return ['success' => true, 'message' => 'Product created successfully'];
        } else {
            return ['success' => false, 'errors' => $model->errors];
        }
    }
    
    return $this->render('create', ['model' => $model]);
}
```

---

## Validation

### Model Validation Rules

```php
public function rules()
{
    return [
        // Required fields
        [['name', 'price'], 'required'],
        
        // String length
        [['name'], 'string', 'min' => 3, 'max' => 255],
        
        // Numbers
        [['price'], 'number', 'min' => 0],
        [['stock'], 'integer', 'min' => 0],
        
        // Email
        [['email'], 'email'],
        
        // URL
        [['website'], 'url'],
        
        // Range
        [['status'], 'in', 'range' => [0, 1]],
        
        // Custom validation
        [['name'], 'validateCustom'],
        
        // Unique
        [['sku'], 'unique'],
        
        // Date
        [['expiry_date'], 'date', 'format' => 'php:Y-m-d'],
    ];
}

// Custom validation method
public function validateCustom($attribute, $params)
{
    if (strlen($this->$attribute) < 5) {
        $this->addError($attribute, 'Name must be at least 5 characters.');
    }
}
```

### Client-Side Validation

```php
<?php $form = ActiveForm::begin([
    'enableClientValidation' => true, // Enable client-side validation
    'enableAjaxValidation' => false,  // Disable AJAX validation
]); ?>
```

---

## Relationships

### One-to-Many

```php
// In Product model
public function getOrderItems()
{
    return $this->hasMany(OrderItem::class, ['product_id' => 'id']);
}

// Usage
$product = Product::findOne(1);
$orderItems = $product->orderItems;
```

### Many-to-One

```php
// In Product model
public function getCategory()
{
    return $this->hasOne(Category::class, ['id' => 'category_id']);
}

// Usage
$product = Product::findOne(1);
$category = $product->category;
```

### Many-to-Many

```php
// In Product model
public function getTags()
{
    return $this->hasMany(Tag::class, ['id' => 'tag_id'])
        ->viaTable('product_tags', ['product_id' => 'id']);
}

// Usage
$product = Product::findOne(1);
$tags = $product->tags;
```

---

## Search and Filtering

### Basic Search

```php
$query = Product::find()
    ->where(['status' => Product::STATUS_ACTIVE])
    ->andWhere(['like', 'name', $searchTerm])
    ->orderBy(['created_at' => SORT_DESC]);
```

### Advanced Search with Multiple Conditions

```php
$query = Product::find();

if ($minPrice) {
    $query->andWhere(['>=', 'price', $minPrice]);
}

if ($maxPrice) {
    $query->andWhere(['<=', 'price', $maxPrice]);
}

if ($categoryIds) {
    $query->andWhere(['in', 'category_id', $categoryIds]);
}

$products = $query->all();
```

---

## Pagination

### In Controller

```php
$dataProvider = new ActiveDataProvider([
    'query' => Product::find()->where(['status' => Product::STATUS_ACTIVE]),
    'pagination' => [
        'pageSize' => 20,
        'pageParam' => 'page',
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
        ],
    ],
]);
```

### In View

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $dataProvider->pagination,
]);
```

---

## AJAX Operations

### AJAX Form Submission

```php
// In view
<?php
$js = <<<JS
$('#product-form').on('beforeSubmit', function(e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        success: function(data) {
            if (data.success) {
                alert('Success!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }
    });
    return false;
});
JS;
$this->registerJs($js);
?>
```

### AJAX Delete

```php
// In controller
public function actionDeleteAjax($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $model = $this->findModel($id);
    if ($model->delete()) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Failed to delete'];
}
```

---

## File Uploads

### Single File Upload

```php
// In controller
$file = UploadedFile::getInstance($model, 'image');
if ($file) {
    $fileName = time() . '_' . $file->name;
    $file->saveAs('uploads/' . $fileName);
    $model->image = $fileName;
}
```

### Multiple File Uploads

```php
// In model
public $images; // Virtual attribute

public function rules()
{
    return [
        [['images'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 5],
    ];
}

// In controller
$files = UploadedFile::getInstances($model, 'images');
foreach ($files as $file) {
    // Process each file
}
```

---

## Best Practices

### 1. Use Scenarios

```php
// In model
public function scenarios()
{
    return [
        self::SCENARIO_DEFAULT => ['name', 'price', 'description'],
        'admin' => ['name', 'price', 'description', 'status', 'stock'],
    ];
}

// Usage
$model->scenario = 'admin';
```

### 2. Use Transactions

```php
$transaction = Yii::$app->db->beginTransaction();
try {
    $product->save();
    $inventory->updateStock();
    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

### 3. Use Events

```php
// In model
public function init()
{
    parent::init();
    $this->on(self::EVENT_AFTER_INSERT, function ($event) {
        // Send notification, update cache, etc.
    });
}
```

### 4. Cache Queries

```php
$products = Yii::$app->cache->getOrSet('products_list', function () {
    return Product::find()->where(['status' => Product::STATUS_ACTIVE])->all();
}, 3600);
```

### 5. Use Data Providers

```php
// Always use DataProvider for lists
$dataProvider = new ActiveDataProvider([
    'query' => Product::find(),
    'pagination' => ['pageSize' => 20],
]);
```

### 6. Security

- Always use `Html::encode()` in views
- Validate all user input
- Use parameterized queries (ActiveRecord does this automatically)
- Implement CSRF protection (enabled by default)
- Use RBAC for access control

---

## Complete Example: API Endpoint

Create `controllers/api/ProductController.php`:

```php
<?php

namespace app\controllers\api;

use Yii;
use app\models\Product;
use yii\rest\ActiveController;
use yii\web\Response;

class ProductController extends ActiveController
{
    public $modelClass = Product::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        return $behaviors;
    }
}
```

---

## Required Configuration Setup

Before implementing your MVC feature, ensure your `config/web.php` has the following essential configurations:

### 1. Path Aliases

Add path aliases to your configuration (required for file uploads, asset management, etc.):

```php
return [
  // ... other config ...
  
  /* Path Aliases */
  'aliases' => [
    '@bower' => '@vendor/bower-asset',
    '@npm' => '@vendor/npm-asset',
    '@webroot' => dirname(__DIR__) . '/public',
    '@web' => '/',
    '@runtime' => dirname(__DIR__) . '/runtime',
    '@vendor' => dirname(__DIR__) . '/vendor',
    '@app' => dirname(__DIR__),
  ],
  
  // ... rest of config ...
];
```

**Why these are needed:**
- `@webroot` - Points to `public/` directory (used for file uploads, assets)
- `@web` - Web-accessible URL path
- `@runtime` - Runtime files (logs, cache)
- `@vendor` - Composer packages
- `@app` - Application root directory

### 2. URL Manager Configuration

Configure URL routing for your controllers:

```php
'components' => [
  // ... other components ...
  
  'urlManager' => [
    'class' => yii\web\UrlManager::class,
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => false,
    'rules' => [
      // Your controller routes (example for Product)
      'product' => 'product/index',
      'product/index' => 'product/index',
      'product/create' => 'product/create',
      'product/view/<id:\d+>' => 'product/view',
      'product/update/<id:\d+>' => 'product/update',
      'product/delete/<id:\d+>' => 'product/delete',
      
      // Default route
      '' => 'site/index',
      // Fallback for any controller/action
      '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    ],
  ],
  
  // ... rest of components ...
],
```

### 3. Asset Manager Configuration

Required for widgets (GridView, Pjax, etc.) to publish CSS/JS files:

```php
'components' => [
  // ... other components ...
  
  'assetManager' => [
    'class' => yii\web\AssetManager::class,
    'basePath' => '@webroot/assets',
    'baseUrl' => '@web/assets',
    'appendTimestamp' => true, // Cache busting
  ],
  
  // ... rest of components ...
],
```

### 4. Create Assets Directory

Create the assets directory that Yii2 needs:

```bash
# Windows
mkdir public\assets

# Linux/Mac
mkdir -p public/assets
```

Create `public/assets/.gitignore`:
```
*
!.gitignore
```

This ensures dynamically generated asset files aren't committed to git.

**Important:** Without the assets directory, you'll get "Error 500: The directory does not exist" when using widgets like GridView or Pjax.

### 5. Layout File Configuration

Ensure your layout file (`views/layouts/main.php`) includes the necessary Yii2 methods for asset bundles and CSRF protection:

```php
<?php

/** @var $this \yii\web\View */
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \yii\helpers\Html::encode($this->title ?? \Yii::$app->name) ?></title>
  <?php $this->registerCsrfMetaTags() ?>
  <?php $this->head() ?>
</head>

<body>
  <?= $content ?>
  <?php $this->endBody() ?>
</body>

</html>
```

**Important methods:**
- `$this->registerCsrfMetaTags()` - Registers CSRF token meta tags (required for forms)
- `$this->head()` - Renders CSS and JavaScript in the `<head>` section
- `$this->endBody()` - Renders JavaScript before closing `</body>` tag

Without these, widgets, forms, and JavaScript features won't work properly.

---

## How Yii2 Connects Your MVC (Automatic Routing)

**Good News:** Your MVC is already connected! Yii2 automatically discovers and routes controllers based on naming conventions.

### How It Works

1. **Controller Discovery:**
   - Yii2 looks in the `controllerNamespace` (set in `config/web.php` as `app\controllers`)
   - Any class in that namespace that extends `yii\web\Controller` is automatically available
   - Your `ProductController` is already connected!

2. **URL Routing:**
   - Controller name: `ProductController` → URL: `/product`
   - Action name: `actionIndex()` → URL: `/product` or `/product/index`
   - Action name: `actionCreate()` → URL: `/product/create`
   - Action name: `actionView($id)` → URL: `/product/view?id=1` or `/product/view/1`

3. **Pretty URLs:**
   - Your `config/web.php` has `enablePrettyUrl => true`
   - This means clean URLs like `/product/create` instead of `/index.php?r=product/create`

### URL Patterns for Your Product Controller

| Controller Action | URL Pattern | Example |
|-------------------|-------------|---------|
| `actionIndex()` | `/product` | `http://localhost/product` |
| `actionIndex()` | `/product/index` | `http://localhost/product/index` |
| `actionView($id)` | `/product/view/1` | `http://localhost/product/view/1` |
| `actionCreate()` | `/product/create` | `http://localhost/product/create` |
| `actionUpdate($id)` | `/product/update/1` | `http://localhost/product/update/1` |
| `actionDelete($id)` | `/product/delete/1` | `http://localhost/product/delete/1` (POST only) |

### No Additional Configuration Needed!

Your `ProductController` is already accessible. Just make sure:
- ✅ Controller is in `controllers/ProductController.php`
- ✅ Namespace is `namespace app\controllers;`
- ✅ Extends `yii\web\Controller`
- ✅ Actions are named `actionXxx()` (camelCase)

---

## Next Steps After Implementation

### 1. Run Database Migrations

After creating all migration files, run them to create the database tables:

```bash
# Run all pending migrations
php yii migrate

# Or if using Docker:
docker-compose exec web php yii migrate
```

**Important Reminders:**
- Migration class names MUST match the filename exactly
- Always use `php yii migrate/create` to generate migrations (don't create them manually)
- Run migrations in order (Yii2 handles this automatically based on timestamps)

### 2. Create Uploads Directory

Create the directory for file uploads (or it will be created automatically on first upload):

```bash
# Windows (PowerShell)
mkdir public\uploads\products

# Linux/Mac
mkdir -p public/uploads/products
```

The controller's `uploadImage()` method will create the directory automatically if it doesn't exist.

### 3. Test Your Application

#### Step 1: Start Your Web Server

**Option A: Using Yii2 Built-in Server (Easiest for Testing)**
```bash
php yii serve
# Server starts at http://localhost:8080
```

**Option B: Using PHP Built-in Server**
```bash
php -S localhost:8000 -t public
# Server starts at http://localhost:8000
```

**Option C: Using Docker (if configured)**
```bash
docker-compose up -d
# Access at http://localhost (or configured port)
```

**Option D: Using Apache/Nginx (Production)**
- Configure your web server to point to the `public/` directory
- Access via your configured domain

#### Step 2: Access Your Product Management

Once your server is running, open your browser and navigate to:

**Main Product List:**
```
http://localhost:8080/product
# or
http://localhost:8000/product
# or
http://localhost/product
```

**Create New Product:**
```
http://localhost:8080/product/create
```

**View Specific Product (after creating one):**
```
http://localhost:8080/product/view/1
```

#### Step 3: Test CRUD Operations

1. **Create (C):**
   - Go to `/product/create`
   - Fill in the form (name, price are required)
   - Upload an image (optional)
   - Click "Save"
   - Should redirect to view page

2. **Read (R):**
   - View list at `/product`
   - Click on a product to view details at `/product/view/1`
   - Test search and filtering in the list

3. **Update (U):**
   - Go to `/product/view/1`
   - Click "Update" button
   - Modify fields
   - Upload new image (optional)
   - Click "Save"
   - Should redirect to view page

4. **Delete (D):**
   - Go to `/product/view/1`
   - Click "Delete" button
   - Confirm deletion
   - Should redirect to list page

5. **Additional Tests:**
   - Test search functionality in the list
   - Test filtering by status, category, etc.
   - Test pagination (if you have many products)
   - Test file uploads with different image formats
   - Test validation (try submitting empty required fields)

#### Step 4: Verify Everything Works

Check the following:
- ✅ No PHP errors in browser
- ✅ No JavaScript errors in browser console (F12)
- ✅ Database tables created correctly
- ✅ Images upload and display correctly
- ✅ Forms submit and save data
- ✅ Search and filter work
- ✅ Pagination works (if applicable)

#### Step 5: Check Logs (if issues occur)

```bash
# View application logs
cat runtime/logs/app.log

# Or on Windows
type runtime\logs\app.log
```

### 4. (Optional) Seed Initial Data

You can create a migration to seed categories or other initial data:

```bash
php yii migrate/create seed_categories
```

Then in the migration file:

```php
public function safeUp()
{
    $this->insert('{{%categories}}', [
        'name' => 'Electronics',
        'status' => 1,
        'created_at' => time(),
        'updated_at' => time(),
    ]);
    
    $this->insert('{{%categories}}', [
        'name' => 'Clothing',
        'status' => 1,
        'created_at' => time(),
        'updated_at' => time(),
    ]);
}
```

### 5. (Optional) Add Foreign Key Constraint

After both `products` and `categories` tables are created, you can add the foreign key constraint. Either:

**Option A:** Uncomment the foreign key code in the products migration and create a new migration to add it:

```bash
php yii migrate/create add_foreign_key_products_category
```

**Option B:** Manually add it to the products migration's `safeUp()` method (uncomment lines 93-100).

---

## Troubleshooting

### Issue: "Class 'app\models\Category' not found"
**Solution:** 
- Make sure `models/Category.php` exists
- Check that the namespace is `namespace app\models;`
- Clear Yii2 cache: `php yii cache/flush`

### Issue: "Table 'categories' doesn't exist"
**Solution:** 
- Run the migrations: `php yii migrate`
- Check that the migration file exists and class name matches filename
- Verify database connection in `.env` file

### Issue: Migration class name doesn't match filename
**Solution:** 
- The class name MUST exactly match the filename
- Use `php yii migrate/create` to generate migrations (don't create manually)
- If you manually created a migration, ensure the class name follows the pattern: `m{timestamp}_{description}`

### Issue: Image upload fails
**Solution:** 
- Check that `public/uploads/products/` directory exists and is writable
- Check file permissions (should be 755 or 777 on Linux)
- Verify `@webroot` alias is correctly configured
- Check PHP `upload_max_filesize` and `post_max_size` settings

### Issue: Category dropdown is empty
**Solution:** 
- This is normal if no categories exist yet
- Create some categories via database or migration (see "Seed Initial Data" above)
- The form will work without categories (category_id is optional)

### Issue: Foreign key constraint fails
**Solution:** 
- Make sure the `categories` table exists before adding the foreign key
- Run the categories migration first, then add the foreign key
- Or uncomment the foreign key code after both tables are created

### Issue: "Error 500: The directory does not exist" (Assets Directory)
**Solution:** 
- This error occurs when Yii2's Asset Manager cannot find the `public/assets/` directory
- Create the directory: `mkdir public/assets` (or `mkdir public\assets` on Windows)
- Ensure the directory is writable: `chmod 755 public/assets` (Linux/Mac)
- Verify AssetManager is configured in `config/web.php` (see "Required Configuration Setup" above)
- Clear cache: `php yii cache/flush cache --interactive=0`

### Issue: Routes not working (404 errors)
**Solution:**
- Verify URL Manager is configured in `config/web.php`
- Check that `enablePrettyUrl => true` is set
- Ensure Apache mod_rewrite is enabled (or Nginx try_files is configured)
- Clear cache: `php yii cache/flush cache --interactive=0`
- Restart web server after configuration changes

### Issue: "Class 'yii\web\UrlManager' not found" or similar
**Solution:**
- Run `composer install` to ensure all dependencies are installed
- Verify vendor directory exists and contains Yii2 framework

### Issue: Aliases not resolving (@webroot, @web, etc.)
**Solution:**
- Verify aliases are defined in `config/web.php` (see "Required Configuration Setup" above)
- Check that alias paths are correct (use absolute paths with `dirname(__DIR__)`)
- Clear cache after adding/modifying aliases

### Issue: "Method Not Allowed" when deleting (POST method error)
**Solution:**
- Delete actions require POST method, not GET
- Use a form with POST method instead of a link with `data-method="post"` (which requires JavaScript)
- Example correct implementation (in view file):
  ```php
  <?php
  use Yii;
  use yii\helpers\Html;
  ?>
  
  <?= Html::beginForm(['delete', 'id' => $model->id], 'post', [
      'style' => 'display: inline-block;',
  ]) ?>
  <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
  <?= Html::submitButton('Delete', [
      'class' => 'btn btn-danger',
      'onclick' => 'return confirm("Are you sure you want to delete this item?");',
  ]) ?>
  <?= Html::endForm() ?>
  ```
- Ensure CSRF token is included in the form
- Make sure the layout includes `<?php $this->head() ?>` and `<?php $this->endBody() ?>` for asset bundles

---

## Summary

This guide covered:
- ✅ Required configuration setup (aliases, URL manager, asset manager)
- ✅ Complete MVC structure
- ✅ Database migrations (with class name matching requirements)
- ✅ Model with validation and relationships
- ✅ Related models (Category example)
- ✅ Controller with CRUD operations
- ✅ Views with forms and lists
- ✅ Search and filtering
- ✅ File uploads
- ✅ AJAX operations
- ✅ Best practices
- ✅ Next steps and troubleshooting
- ✅ Common errors and solutions

Follow these patterns to build any feature in your Yii2 application!

## Quick Reference: Essential Setup Checklist

Before implementing any MVC feature, ensure:

1. ✅ **Path Aliases** are configured in `config/web.php` (see "Required Configuration Setup")
2. ✅ **URL Manager** is configured with routing rules (see "Required Configuration Setup")
3. ✅ **Asset Manager** is configured (see "Required Configuration Setup")
4. ✅ **Layout file** includes `$this->head()` and `$this->endBody()` (see "Required Configuration Setup")
5. ✅ **Assets directory** exists: `public/assets/` (create with `mkdir public/assets`)
6. ✅ **Uploads directory** exists (or will be created automatically by controller)
7. ✅ **Database connection** is configured in `.env` file
8. ✅ **Migrations** are run: `php yii migrate` (or `docker-compose exec web php yii migrate`)
9. ✅ **Cache** is cleared after configuration changes: `php yii cache/flush cache --interactive=0`
10. ✅ **All use statements** are included in view files (`use Yii;`, `use app\models\Product;`, etc.)

## Complete File Checklist

After following this guide, you should have:

**Migrations:**
- ✅ `migrations/mXXXXXX_XXXXXX_create_products_table.php`
- ✅ `migrations/mXXXXXX_XXXXXX_create_categories_table.php` (optional)

**Models:**
- ✅ `models/Product.php`
- ✅ `models/ProductSearch.php`
- ✅ `models/Category.php` (optional, if using categories)

**Controller:**
- ✅ `controllers/ProductController.php`

**Views:**
- ✅ `views/product/index.php`
- ✅ `views/product/view.php`
- ✅ `views/product/create.php`
- ✅ `views/product/update.php`
- ✅ `views/product/_form.php`
- ✅ `views/layouts/main.php` (with proper head/endBody methods)

**Configuration:**
- ✅ `config/web.php` (with aliases, urlManager, assetManager)
- ✅ `public/assets/` directory exists
- ✅ `public/assets/.gitignore` file exists

**Database:**
- ✅ Migrations have been run
- ✅ Tables exist in database

If all items are checked, your MVC feature should work without errors!

---

## 🎓 Learning Path

**For Beginners:**
1. Start with "Understanding MVC in Yii2"
2. Follow "Creating a Complete Feature: Step-by-Step"
3. Implement the Product Management example
4. Review "Best Practices" section

**For Experienced Developers:**
1. Review "Required Configuration Setup"
2. Check "How Yii2 Connects Your MVC"
3. Reference specific sections as needed
4. Review troubleshooting for common issues

---

## 📝 Code Examples

All code examples in this guide are:
- ✅ Production-ready
- ✅ Following Yii2 best practices
- ✅ Complete and tested
- ✅ Ready to copy and use

**Note:** Replace placeholder values (like migration timestamps `mXXXXXX_XXXXXX`) with actual values generated by Yii2 commands.

---

## 🔗 Related Resources

- [Yii2 Official Documentation](https://www.yiiframework.com/doc/guide/2.0/en)
- [Yii2 API Reference](https://www.yiiframework.com/doc/api/2.0)
- [Project README](README.md) - Setup and installation
- [Console Commands Guide](GUIDE_CONSOLE_COMMANDS.md) - Creating console commands

---

## 🤝 Contributing

Found an issue or want to improve this guide? Contributions are welcome!

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

## 📄 License

This guide is part of the Yii2 Starter MVC project. See the main repository for license information.

---

## ⚠️ Important Notes

1. **Migration Class Names**: Always use the exact class name generated by `php yii migrate/create`. The class name MUST match the filename exactly.

2. **Configuration First**: Before implementing any MVC feature, complete the "Required Configuration Setup" section. Missing configuration will cause errors.

3. **Database First**: Run migrations before testing your MVC feature. The guide includes error handling for missing tables, but it's better to run migrations first.

4. **Copy-Paste Ready**: All code examples are complete and ready to use. Just replace placeholder values (like migration timestamps) with your actual values.

5. **Testing**: After implementing, test each CRUD operation:
   - Create a record
   - View the list
   - View a single record
   - Update a record
   - Delete a record
   - Test search and filtering

---

## 📚 Additional Learning

- **Yii2 Active Record**: Learn more about database operations at [Yii2 Guide - Active Record](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
- **Yii2 Widgets**: Explore available widgets at [Yii2 Widgets](https://www.yiiframework.com/doc/guide/2.0/en/input-widgets)
- **Yii2 Security**: Security best practices at [Yii2 Security](https://www.yiiframework.com/doc/guide/2.0/en/security-overview)

---

**Version:** 1.0  
**Last Updated:** 2026  
**Yii2 Version:** 2.0.x compatible

---

## ✅ Final Verification Checklist

Before deploying to production, verify all items:

- [ ] All migrations run successfully without errors
- [ ] All files are created in correct locations with proper namespaces
- [ ] All `use` statements are included in every file
- [ ] Configuration is complete (aliases, urlManager, assetManager in `config/web.php`)
- [ ] Layout file includes `$this->head()` and `$this->endBody()`
- [ ] Assets directory exists: `public/assets/` (with `.gitignore`)
- [ ] Uploads directory is writable (or will be created automatically)
- [ ] All CRUD operations work (Create, Read, Update, Delete)
- [ ] Search and filtering work correctly
- [ ] File uploads work (if implemented)
- [ ] Delete operations use POST method (not GET)
- [ ] No PHP errors in browser or logs (`runtime/logs/app.log`)
- [ ] No JavaScript console errors
- [ ] Forms submit correctly with validation
- [ ] CSRF protection is working (forms include CSRF tokens)
- [ ] Database tables exist and are accessible
- [ ] Relationships work (e.g., product.category)

**If all items are checked ✅, your MVC feature is production-ready!**

---

**Happy Coding! 🚀**

*This guide is maintained as part of the Yii2 Starter MVC project. For issues, questions, or improvements, please open an issue or submit a pull request on GitHub.*

