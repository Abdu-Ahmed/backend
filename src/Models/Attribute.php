<?php

namespace App\Models;

/**
 * Abstract Attribute model that provides common functionality for all attribute types.
 */
abstract class Attribute extends Model
{
    /**
     * @var string|null The attribute ID.
     */
    protected ?string $id = null;
/**
     * @var string The attribute name.
     */
    protected string $name;
/**
     * @var string The attribute type.
     */
    protected string $type;
/**
     * @var array The attribute items.
     */
    protected array $items = [];
/**
     * Constructor.
     *
     * @param array $data Attribute data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct();
        if (!empty($data)) {
        // Ensure we use the value from the DB if provided
            $this->id    = $data['id'] ?? null;
            $this->name  = $data['name'];
            $this->type  = $data['type'];
            $this->items = $data['items'] ?? [];
        }
    }

    /**
     * Get the attribute ID.
     *
     * @return string
     */
    public function getId(): string
    {
        // If $this->id is null or empty, fallback to a generated value.
        return !empty($this->id) ? $this->id : 'attr-' . uniqid();
    }

    /**
     * Get the attribute name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the attribute type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the attribute items.
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get the default value.
     *
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        if (empty($this->items)) {
            return null;
        }

        return $this->items[0]['value'];
    }

    /**
     * Convert attribute to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'name'       => $this->getName(),
            'type'       => $this->getType(),
            'items'      => $this->getItems(),
            '__typename' => 'Attribute'
        ];
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected static function tableName(): string
    {
        return 'attributes';
    }

    /**
     * Create an attribute from database data.
     *
     * @param array $data
     * @return self
     */
    protected static function hydrate(array $data): self
    {
        // Fetch items for this attribute if not already included.
        if (!isset($data['items'])) {
            $model = new static();
            $stmtItems = $model->db->prepare("SELECT id, displayValue, value, __typename FROM attribute_items WHERE attribute_id = :attribute_id");
            $stmtItems->execute([':attribute_id' => $data['id']]);
            $data['items'] = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
        }

        return AttributeFactory::create($data);
    }
}
